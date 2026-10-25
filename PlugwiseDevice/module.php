<?php

declare(strict_types=1);

/**
 * @package       Plugwise
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2017-2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       2.10
 */
require_once __DIR__ . '/../libs/Plugwise.php';  // diverse Klassen
eval('declare(strict_types=1);namespace PlugwiseDevice {?>' . file_get_contents(__DIR__ . '/../libs/helper/VariableHelper.php') . '}');
/**
 * PlugwiseDevice Klasse für die Nodes des Plugwise-Netzwerk.
 * Erweitert IPSModule.
 *
 * @package       Plugwise
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2017-2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       2.10
 * @property int $ParentID Die InstanzeID des IO-Parent
 * @property int  $Type Typ des Gerätes
 * @property bool $isCalib
 * @property float $gainA
 * @property float $gainB
 * @property float $offTot
 * @property float $offRuis
 * @property int $LastTimeSync Zeitpunkt (Unix-Zeit) der letzten automatischen Korrektur der Uhr des Nodes
 * @method void SetValueBoolean(string $Ident, bool $value)
 * @method void SetValueFloat(string $Ident, float $value)
 * @method void SetValueInteger(string $Ident, int $value)
 * @method void SetValueString(string $Ident, string $value)
 * @method int RegisterParent()
 * @method bool IORequestAction(string $Ident, mixed $Value)
 * @method void IOMessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data)
 */
class PlugwiseDevice extends IPSModuleStrict
{
    use \Plugwise\BufferHelper,
        \Plugwise\DebugHelper,
        \Plugwise\FormHelper,
        \PlugwiseDevice\VariableHelper,
        \Plugwise\VariableProfileHelper,
        \Plugwise\InstanceStatus {
            \Plugwise\InstanceStatus::MessageSink as IOMessageSink;
            \Plugwise\InstanceStatus::RequestAction as IORequestAction;
        }
    /** Instanzstatus, wenn der Node nicht antwortet. */
    private const StatusNodeNotReachable = IS_EBASE + 1;

    /** Attribut: zuletzt ermittelter Node-Typ. */
    private const AttributeNodeType = 'NodeTypeCache';

    /** Attribut: MAC des Nodes, zu dem der gespeicherte Typ gehört. */
    private const AttributeNodeTypeMAC = 'NodeTypeCacheMAC';

    /**
     * Create
     *
     * @return void
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyString(\Plugwise\Node\Property::NodeMAC, '');
        $this->RegisterPropertyInteger(\Plugwise\Node\Property::Interval, 5);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowState, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowCurrent, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowAverage, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowOverall, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowFeedIn, false);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowFrequenz, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowTemperature, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowHumidity, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowTime, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowFirmware, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::ShowHardware, true);
        $this->RegisterTimer(\Plugwise\Node\Timer::RequestState, 0, 'IPS_RequestAction($_IPS[\'TARGET\'], \'TimerEvent\', 0);');
        $this->RegisterTimer(\Plugwise\Node\Timer::SetTime, 0, 'IPS_RequestAction($_IPS[\'TARGET\'], \'SetTime\', 0);');
        // Der Node-Typ muss einen Neustart überdauern, weil batteriebetriebene Nodes (Scan, Sense, Switch) nur selten antworten.
        $this->RegisterAttributeInteger(self::AttributeNodeType, \Plugwise\NodeTyp::Unknown);
        $this->RegisterAttributeString(self::AttributeNodeTypeMAC, '');
        $this->ParentID = 0;
        $this->isCalib = false;
        $this->Type = \Plugwise\NodeTyp::Unknown;
        $this->gainA = 0;
        $this->gainB = 0;
        $this->offTot = 0;
        $this->offRuis = 0;
        $this->LastTimeSync = 0;
    }

    /**
     * ApplyChanges
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
        $this->ParentID = 0;
        $this->isCalib = false;
        $this->Type = \Plugwise\NodeTyp::Unknown;
        $this->gainA = 0;
        $this->gainB = 0;
        $this->offTot = 0;
        $this->offRuis = 0;
        $this->LastTimeSync = 0;
        parent::ApplyChanges();
        $NodeMAC = strtoupper(trim($this->ReadPropertyString(\Plugwise\Node\Property::NodeMAC)));
        // Gespeicherten Typ nur übernehmen, wenn er zu dieser MAC gehört.
        if (($NodeMAC != '') && ($this->ReadAttributeString(self::AttributeNodeTypeMAC) === $NodeMAC)) {
            $this->Type = $this->ReadAttributeInteger(self::AttributeNodeType);
        }
        if ($NodeMAC == '') {
            $Filter = '.*"NodeMAC":"999999999".*';
        } else {
            // Der Splitter überträgt die MAC hex-kodiert (bin2hex) im JSON.
            $Filter = '.*"NodeMAC":"' . bin2hex($NodeMAC) . '".*';
        }
        $this->SetReceiveDataFilter($Filter);
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->RegisterParent();
        $this->RegisterVariableBoolean(
            'Reachable',
            $this->Translate('Reachable'),
            \Plugwise\Presentation::Reachable($this->Translate('Alert'), 'OK')
        );
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowState)) {
            $this->UnregisterVariable('State');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowFrequenz)) {
            $this->UnregisterVariable('Frequenz');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowTime)) {
            $this->UnregisterVariable('Timestamp');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowCurrent)) {
            $this->UnregisterVariable('PowerCurrent');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowAverage)) {
            $this->UnregisterVariable('PowerAverage');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowOverall)) {
            $this->UnregisterVariable('ConsumptionOverall');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowFeedIn)) {
            $this->UnregisterVariable('FeedInOverall');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowHardware)) {
            $this->UnregisterVariable('Hardware');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowFirmware)) {
            $this->UnregisterVariable('Firmware');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowTemperature)) {
            $this->UnregisterVariable('Temperature');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowHumidity)) {
            $this->UnregisterVariable('Humidity');
        }
        if ($NodeMAC != '' && $this->HasActiveParent()) {
            $this->IOChangeState(IS_ACTIVE);
        } else {
            $this->IOChangeState(IS_INACTIVE);
        }
    }

    /**
     * MessageSink
     *
     * @param  int $TimeStamp
     * @param  int $SenderID
     * @param  int $Message
     * @param  array $Data
     * @return void
     */
    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        $this->IOMessageSink($TimeStamp, $SenderID, $Message, $Data);
    }

    /**
     * GetConfigurationForm
     *
     * @return string
     */
    public function GetConfigurationForm(): string
    {
        $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        if ($this->GetStatus() == IS_CREATING) {
            return json_encode($Form);
        }
        $this->SendDebug('GetConfigurationForm', \Plugwise\NodeTyp::ToString($this->Type), 0);
        switch ($this->Type) {
            case \Plugwise\NodeTyp::Circle:
            case \Plugwise\NodeTyp::CirclePlus:
            case \Plugwise\NodeTyp::Stealth:
                $this->SetFormProperty($Form, 'Sens', 'visible', false);
                break;
            case \Plugwise\NodeTyp::Scan:
            case \Plugwise\NodeTyp::Switch:
                $this->SetFormProperty($Form, 'Aktor', 'visible', false);
                $this->SetFormProperty($Form, 'Sens', 'visible', false);
                $this->SetFormProperty($Form, 'ReadCalibration', 'visible', false);
                $this->SetFormProperty($Form, 'RequestEnergy', 'visible', false);
                break;
            case \Plugwise\NodeTyp::Sense:
                $this->SetFormProperty($Form, 'Aktor', 'visible', false);
                $this->SetFormProperty($Form, 'ReadCalibration', 'visible', false);
                $this->SetFormProperty($Form, 'RequestEnergy', 'visible', false);
                break;
        }
        return json_encode($Form);
    }

    /**
     * RequestAction
     *
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     *
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        if ($this->IORequestAction($Ident, $Value)) {
            return;
        }
        switch ($Ident) {
            case 'State':
                $this->SwitchMode((bool) $Value);
                break;
            case 'TimerEvent':
                $this->TimerEvent();
                break;
            case 'SetTime':
                $this->SetTime();
                break;
            default:
                echo $this->Translate('Invalid Ident') . PHP_EOL;
                break;
        }
    }

    /**
     * SwitchMode
     *
     * IPS-Instanz-Funktion 'PLUGWISE_SwitchMode'. Schaltet den Circle ein oder aus.
     *
     * @param bool $Value True für Ein,  False für aus.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SwitchMode(bool $Value): bool
    {
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowState)) {
            echo $this->Translate('Switch state not active in instance') . PHP_EOL;
            return false;
        }
        $PlugwiseData = new \Plugwise\Frame(\Plugwise\Command::SwitchRequest, null, ($Value ? \Plugwise\SwitchState::On : \Plugwise\SwitchState::Off));
        $ret = $this->Send($PlugwiseData);
        if ($ret === false) {
            return false;
        }
        $Ok = (substr($ret->Data, 0, 4) == ($Value ? \Plugwise\AckMsg::SWITCHON : \Plugwise\AckMsg::SWITCHOFF));
        if ($Ok) {
            $this->SetValueBoolean('State', $Value);
            return true;
        }
        echo $this->Translate('Error on send switch state') . PHP_EOL;
        return false;
    }

    /**
     * RequestState
     *
     * IPS-Instanz-Funktion 'PLUGWISE_RequestState'.
     *
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState(): bool
    {
        $PlugwiseData = new \Plugwise\Frame(\Plugwise\Command::InfoRequest);
        $Result = $this->Send($PlugwiseData);
        if ($Result == false || strlen($Result->Data) != 42) {
            return false;
        }
        if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowHardware)) {
            $this->RegisterVariableString(
                'Hardware',
                $this->Translate('Hardware'),
                []
            );
            $Hardware = sprintf('%s-%s-%s', substr($Result->Data, 20, 4), substr($Result->Data, 24, 4), substr($Result->Data, 28, 4));
            $this->SetValueString('Hardware', $Hardware);
        }
        if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowFirmware)) {
            $this->RegisterVariableInteger(
                'Firmware',
                $this->Translate('Firmware'),
                [
                    'PRESENTATION'    => VARIABLE_PRESENTATION_DATE_TIME,
                    'TEMPLATE'        => VARIABLE_TEMPLATE_DATE
                ]
            );
            $Firmware = \Plugwise\Frame::HexToInt(substr($Result->Data, 32, 8));
            $this->SetValueInteger('Firmware', $Firmware);
        }
        $TypeCode = substr($Result->Data, 40, 2);
        $this->Type = \Plugwise\NodeTyp::FromHex($TypeCode);
        if ($this->Type == \Plugwise\NodeTyp::Unknown) {
            $this->SendDebug('Unknown node type', $TypeCode, 0);
        } else {
            $this->WriteAttributeInteger(self::AttributeNodeType, $this->Type);
            $this->WriteAttributeString(self::AttributeNodeTypeMAC, strtoupper(trim($this->ReadPropertyString(\Plugwise\Node\Property::NodeMAC))));
        }
        $this->SetSummary(\Plugwise\NodeTyp::ToString($this->Type));
        $this->SendDebug('Type', \Plugwise\NodeTyp::ToString($this->Type), 0);
        switch ($this->Type) {
            case \Plugwise\NodeTyp::Circle:
            case \Plugwise\NodeTyp::CirclePlus:
            case \Plugwise\NodeTyp::Stealth:
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowState)) {
                    $this->RegisterVariableBoolean(
                        'State',
                        $this->Translate('State'),
                        [
                            'PRESENTATION' => VARIABLE_PRESENTATION_SWITCH
                        ]
                    );
                    $this->EnableAction('State');
                    $Value = (substr($Result->Data, 16, 2) == \Plugwise\SwitchState::On);
                    $this->SetValueBoolean('State', $Value);
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowFrequenz)) {
                    $this->RegisterVariableFloat(
                        'Frequenz',
                        $this->Translate('Frequency'),
                        \Plugwise\Presentation::Frequency()
                    );
                    $Frequenz = \Plugwise\SwitchState::ToHertz(\Plugwise\Frame::HexToInt(substr($Result->Data, 18, 2)));
                    if ($Frequenz === null) {
                        $this->SendDebug('Unknown frequency code', substr($Result->Data, 18, 2), 0);
                    } else {
                        $this->SetValueFloat('Frequenz', $Frequenz);
                    }
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowTime)) {
                    $this->RegisterVariableInteger(
                        'Timestamp',
                        $this->Translate('Timestamp'),
                        [
                            'PRESENTATION'    => VARIABLE_PRESENTATION_DATE_TIME,
                            'TEMPLATE'        => VARIABLE_TEMPLATE_DATE_TIME
                        ]
                    );
                    $Timestamp = \Plugwise\Frame::Hex2Timestamp(substr($Result->Data, 0, 8));
                    if ($Timestamp > 0) {
                        $this->SetValueInteger('Timestamp', $Timestamp);
                        $this->SendDebug('Timestamp', gmdate('H:i:s d.m.Y', $Timestamp), 0);
                    } else {
                        $this->SendDebug('Timestamp', 'invalid: ' . substr($Result->Data, 0, 8), 0);
                    }
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowCurrent)) {
                    $this->RegisterVariableFloat(
                        'PowerCurrent',
                        $this->Translate('Power current'),
                        \Plugwise\Presentation::Power()
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowAverage)) {
                    $this->RegisterVariableFloat(
                        'PowerAverage',
                        $this->Translate('Power average'),
                        \Plugwise\Presentation::Power()
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowOverall)) {
                    $this->RegisterVariableFloat(
                        'ConsumptionOverall',
                        $this->Translate('Consumption overall'),
                        \Plugwise\Presentation::EnergyOverall()
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowFeedIn)) {
                    $this->RegisterVariableFloat(
                        'FeedInOverall',
                        $this->Translate('Feed-in overall'),
                        \Plugwise\Presentation::EnergyOverall()
                    );
                }
                break;
            case \Plugwise\NodeTyp::Sense:
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowTemperature)) {
                    $this->RegisterVariableFloat(
                        'Temperature',
                        $this->Translate('Temperature'),
                        \Plugwise\Presentation::Temperature()
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowHumidity)) {
                    $this->RegisterVariableFloat(
                        'Humidity',
                        $this->Translate('Humidity'),
                        \Plugwise\Presentation::Humidity()
                    );
                }
                break;
        }
        // Hat der Node seine Uhr verloren (z.B. nach einem Stromausfall), wird die Zeit sofort gesendet, statt auf den Stundentimer
        // zu warten. Höchstens alle 5 Minuten, falls der Node die Zeit nicht übernimmt.
        if ($this->IsActuator() && \Plugwise\Frame::NodeTimeNeedsSync(\Plugwise\Frame::Hex2Timestamp(substr($Result->Data, 0, 8)), time()) && ((time() - $this->LastTimeSync) > 300)) {
            $this->SendDebug('Node time', 'Deviation, set time', 0);
            $this->LastTimeSync = time();
            $this->SetTime();
        }
        return true;
    }

    /**
     * RequestCalibration
     *
     * IPS-Instanz-Funktion 'PLUGWISE_RequestCalibration'.
     *
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestCalibration(): bool
    {
        $PlugwiseData = new \Plugwise\Frame(\Plugwise\Command::CalibrationRequest);
        $Result = $this->Send($PlugwiseData);
        if ($Result == false) {
            return false;
        }
        $Values = str_split($Result->Data, 8);
        $Calibration = [];
        // Fehlende Kalibrierwerte sendet der Node als FFFFFFFF, das ergibt als Float NAN und würde alle Berechnungen verfälschen.
        if (count($Values) >= 4 && strlen($Values[3]) == 8) {
            foreach (array_slice($Values, 0, 4) as $Value) {
                $Calibration[] = \Plugwise\Frame::Hex2Float($Value);
            }
        }
        if ((count($Calibration) != 4) || !array_reduce($Calibration, fn (bool $Valid, float $Value) => $Valid && is_finite($Value), true)) {
            $this->SendDebug('Calibration', 'invalid: ' . $Result->Data, 0);
            echo $this->Translate('Calibration data invalid') . PHP_EOL;
            return false;
        }
        $this->isCalib = true;
        [$this->gainA, $this->gainB, $this->offTot, $this->offRuis] = $Calibration;
        $this->SendDebug('gainA', $this->gainA, 0);
        $this->SendDebug('gainB', $this->gainB, 0);
        $this->SendDebug('offTot', $this->offTot, 0);
        $this->SendDebug('offRuis', $this->offRuis, 0);
        return true;
    }

    /**
     * RequestEnergy
     *
     * IPS-Instanz-Funktion 'PLUGWISE_RequestEnergy'.
     *
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestEnergy(): bool
    {
        $PlugwiseData = new \Plugwise\Frame(\Plugwise\Command::PowerUsageRequest);
        $Result = $this->Send($PlugwiseData);
        if ($Result == false) {
            return false;
        }
        if (!$this->isCalib) {
            if (!$this->RequestCalibration()) {
                $this->SendDebug('No calibration found', '', 0);
                echo $this->Translate('No calibration found') . PHP_EOL;
                return false;
            }
        }
        $Pulses1Hex = substr($Result->Data, 0, 4);
        $Pulses2Hex = substr($Result->Data, 4, 4);
        $PulsesTotalHex = substr($Result->Data, 8, 8);
        if ($Pulses1Hex != 'FFFF' && $this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowCurrent)) {
            $Pulses = \Plugwise\Frame::pulsesCorrection(
                $Pulses1Hex,
                1,
                $this->offRuis,
                $this->offTot,
                $this->gainA,
                $this->gainB
            );
            $Watt = \Plugwise\Frame::pulsesToWatt($Pulses);
            $this->SendDebug('PowerCurrent', $Watt, 0);
            $this->SetValueFloat('PowerCurrent', $Watt);
        }
        if ($Pulses2Hex != 'FFFF' && $this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowAverage)) {
            $Pulses = \Plugwise\Frame::pulsesCorrection(
                $Pulses2Hex,
                8,
                $this->offRuis,
                $this->offTot,
                $this->gainA,
                $this->gainB
            );
            $Watt = \Plugwise\Frame::pulsesToWatt($Pulses);
            $this->SendDebug('PowerAverage', $Watt, 0);
            $this->SetValueFloat('PowerAverage', $Watt);
        }
        // Pulse und Zähler sind vorzeichenbehaftet (Zweierkomplement): Speist ein Gerät zurück (z.B. ein Wechselrichter),
        // sind sie negativ. pulsesCorrection behält das Vorzeichen, Leistung und Energie werden dann negativ.
        if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowOverall)) {
            $Pulses = \Plugwise\Frame::pulsesCorrection(
                $PulsesTotalHex,
                3600,
                $this->offRuis,
                $this->offTot,
                $this->gainA,
                $this->gainB
            );
            $WattTotal = \Plugwise\Frame::pulsesToKwh($Pulses);
            $this->SendDebug('Consumption total', $WattTotal, 0);
            $this->SetValueFloat('ConsumptionOverall', $WattTotal);
        }
        // Zweiter Zähler (erzeugte Energie): Speist ein Gerät zurück, zählt er negativ. Die Einspeisung wird positiv dargestellt.
        // Ältere Firmware sendet ihn eventuell nicht, die Variable behält dann ihren Wert.
        $PulsesProducedHex = substr($Result->Data, 16, 8);
        if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowFeedIn) && (strlen($PulsesProducedHex) == 8)) {
            $Pulses = \Plugwise\Frame::pulsesCorrection(
                $PulsesProducedHex,
                3600,
                $this->offRuis,
                $this->offTot,
                $this->gainA,
                $this->gainB
            );
            $FeedIn = -\Plugwise\Frame::pulsesToKwh($Pulses);
            $this->SendDebug('Feed-in total', $FeedIn, 0);
            $this->SetValueFloat('FeedInOverall', $FeedIn);
        }
        return true;
    }

    /**
     * ReceiveData
     *
     * Interne SDK-Funktion. Empfängt Datenpakete vom PlugwiseSplitter.
     *
     * @param string $JSONString Das Datenpaket als JSON formatierter String.
     * @return string
     */
    public function ReceiveData(string $JSONString): string
    {
        $Data = json_decode($JSONString);
        $PlugwiseData = new \Plugwise\Frame($Data);
        $this->Decode($PlugwiseData);
        return '';
    }

    /**
     * IOChangeState
     *
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     *
     * @param  int $State
     * @return void
     */
    protected function IOChangeState(int $State): void
    {
        $this->SetTimerInterval(\Plugwise\Node\Timer::RequestState, 0);
        $this->SetTimerInterval(\Plugwise\Node\Timer::SetTime, 0);
        // Ohne MAC (z.B. per Skript angelegt) oder ohne aktiven Parent tut die Instanz nichts, der Status bleibt unverändert.
        if (($State != IS_ACTIVE) || (trim($this->ReadPropertyString(\Plugwise\Node\Property::NodeMAC)) == '') || !$this->HasActiveParent()) {
            return;
        }
        if ($this->RequestState()) {
            $this->SetTime();
            if ($this->IsActuator()) {
                $this->RequestCalibration();
                $this->RequestEnergy();
            }
        }
        if ($this->ReadPropertyInteger(\Plugwise\Node\Property::Interval) > 0) {
            $this->SetTimerInterval(\Plugwise\Node\Timer::RequestState, $this->ReadPropertyInteger(\Plugwise\Node\Property::Interval) * 1000);
        }
        $this->SetTimerInterval(\Plugwise\Node\Timer::SetTime, 60 * 60 * 1000);
    }

    /**
     * TimerEvent
     *
     * @return void
     */
    private function TimerEvent(): void
    {
        if ($this->RequestState()) {
            if ($this->IsActuator()) {
                $this->RequestEnergy();
            }
        }
    }

    /**
     * SetTime
     *
     * @return bool
     */
    private function SetTime(): bool
    {
        $PlugwiseData = new \Plugwise\Frame(\Plugwise\Command::ClockSetRequest, null, \Plugwise\Frame::Timestamp2Hex(time()));
        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            return false;
        }
        if (substr($Result->Data, 0, 4) != \Plugwise\AckMsg::SETTIMEACK) {
            echo $this->Translate('Error on set time in Node') . PHP_EOL;
            return false;
        }
        return true;
    }

    /**
     * Decode
     *
     * Dekodiert die empfangenen Events und Antworten.
     *
     * @param \Plugwise\Frame $PlugwiseData Der zu dekodierende Datensatz als Objekt.
     * @return void
     */
    private function Decode(\Plugwise\Frame $PlugwiseData): void
    {
        $this->SendDebug('EVENT', $PlugwiseData, 0);

        switch ($PlugwiseData->Command) {
            case \Plugwise\Command::NodeAwakeResponse: // Batteriebetriebener Node ist aufgewacht (Grund: 1 Byte).
                $AwakeType = \Plugwise\Frame::HexToInt(substr($PlugwiseData->Data, 0, 2));
                $this->SendDebug('Node awake', \Plugwise\NodeAwake::ToString($AwakeType), 0);
                $this->SetReachable(true);
                break;
            case \Plugwise\Command::NodeSwitchGroupResponse: // Schaltbedingung einer Gruppe: Gruppe (1 Byte) und Zustand (1 Byte).
                $SwitchGroup = \Plugwise\SwitchState::FromGroupData($PlugwiseData->Data);
                if ($SwitchGroup === null) {
                    $this->SendDebug('Switch group', 'Invalid data: ' . $PlugwiseData->Data, 0);
                    break;
                }
                $this->SendDebug('Switch group', $SwitchGroup, 0);
                switch ($this->Type) {
                    case \Plugwise\NodeTyp::Scan: // Bewegung ein/aus
                        $this->RegisterVariableBoolean('Motion', $this->Translate('Motion'), \Plugwise\Presentation::Motion($this->Translate('No motion'), $this->Translate('Motion')));
                        $this->SetValueBoolean('Motion', $SwitchGroup['State']);
                        break;
                    case \Plugwise\NodeTyp::Switch: // Welche Gruppe zu welcher Taste gehört, ist nicht belegt (nur Gruppe 1 und 2 bekannt).
                        if (($SwitchGroup['Group'] == 1) || ($SwitchGroup['Group'] == 2)) {
                            $Ident = 'Switch' . $SwitchGroup['Group'];
                            $this->RegisterVariableBoolean($Ident, $this->Translate('Switch ' . $SwitchGroup['Group']), []);
                            $this->SetValueBoolean($Ident, $SwitchGroup['State']);
                        }
                        break;
                    default: // z.B. Hysterese des Sense (Gruppe 1 Feuchte, Gruppe 2 Temperatur): wird nicht ausgewertet.
                        break;
                }
                break;
            case \Plugwise\Command::SensInfoResponse: //sens
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowTemperature)) {
                    $Temperature = \Plugwise\Sense::Temperature(\Plugwise\Frame::HexToInt(substr($PlugwiseData->Data, 4, 4)));
                    if ($Temperature !== null) {
                        $this->SetValueFloat('Temperature', $Temperature);
                    }
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::ShowHumidity)) {
                    $Humidity = \Plugwise\Sense::Humidity(\Plugwise\Frame::HexToInt(substr($PlugwiseData->Data, 0, 4)));
                    if ($Humidity !== null) {
                        $this->SetValueFloat('Humidity', $Humidity);
                    }
                }
                break;
            default:
                break;
        }
    }

    /**
     * IsActuator
     *
     * @return bool true bei Circle, Circle+ und Stealth.
     */
    private function IsActuator(): bool
    {
        return in_array($this->Type, [\Plugwise\NodeTyp::Circle, \Plugwise\NodeTyp::CirclePlus, \Plugwise\NodeTyp::Stealth]);
    }

    /**
     * SetReachable
     *
     * Setzt die Statusvariable und den Instanzstatus, damit ein Watchdog den Ausfall erkennen kann.
     *
     * @param  bool $Reachable
     * @return void
     */
    private function SetReachable(bool $Reachable): void
    {
        $this->SetValueBoolean('Reachable', $Reachable);
        $Status = $this->GetStatus();
        if (!$Reachable && ($Status == IS_ACTIVE)) {
            $this->SetStatus(self::StatusNodeNotReachable);
        } elseif ($Reachable && ($Status == self::StatusNodeNotReachable)) {
            $this->SetStatus(IS_ACTIVE);
        }
    }
    /**
     * Send
     *
     * Konvertiert $Data zu einem JSONString und versendet diese an den Splitter.
     *
     * @param \Plugwise\Frame $PlugwiseData Zu versendende Daten.
     * @return \Plugwise\Frame|bool Objekt mit der Antwort. false im Fehlerfall.
     */
    private function Send(\Plugwise\Frame $PlugwiseData): bool|\Plugwise\Frame
    {
        $NodeMAC = trim($this->ReadPropertyString(\Plugwise\Node\Property::NodeMAC));
        if ($NodeMAC == '') {
            echo $this->Translate('Device MAC is not configured.') . PHP_EOL;
            return false;
        }
        $this->SendDebug('Send', $PlugwiseData, 0);
        $PlugwiseData->NodeMAC = $NodeMAC;
        $JSONData = $PlugwiseData->ToJSONStringForSplitter();
        $ResultString = @$this->SendDataToParent($JSONData);
        $this->SendDebug('Send', $JSONData, 0);
        if ($ResultString == false) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            $this->SetReachable(false);
            echo $this->Translate('Timeout error') . PHP_EOL;
            return false;
        }
        /** @var \Plugwise\Frame|bool $Result */
        $Result = @unserialize($ResultString, ['allowed_classes' => [\Plugwise\Frame::class]]);
        $this->SendDebug('Response', $Result, 0);
        if (($Result === null) || ($Result === false)) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            $this->SetReachable(false);
            echo $this->Translate('Error on send data') . PHP_EOL;
            return false;
        }
        // Ack-Frames enthalten hinter dem Code noch die MAC des Nodes, daher nur den Code (4 Zeichen) vergleichen.
        if (($Result->Command === \Plugwise\Command::AckMsgResponse) && (substr((string) $Result->Data, 0, 4) === \Plugwise\AckMsg::OUTOFRANGE)) {
            $this->SetReachable(false);
            echo $this->Translate('Node not reachable') . PHP_EOL;
            return false;
        }
        $this->SetReachable(true);
        return $Result;
    }
}
