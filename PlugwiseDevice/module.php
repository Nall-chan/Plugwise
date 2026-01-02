<?php

declare(strict_types=1);

/**
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2026 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.00
 * @example <b>Ohne</b>
 */
require_once __DIR__ . '/../libs/Plugwise.php';  // diverse Klassen
eval('declare(strict_types=1);namespace PlugwiseDevice {?>' . file_get_contents(__DIR__ . '/../libs/helper/VariableHelper.php') . '}');
/**
 * PlugwiseDevice Klasse für die Nodes des Plugwise-Netzwerk.
 * Erweitert IPSModule.
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2026 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.00
 * @example <b>Ohne</b>
 * @property int $ParentID Die InstanzeID des IO-Parent
 * @property int  $Type Typ des Gerätes
 * @property bool $isCalib
 * @property float $gainA
 * @property float $gainB
 * @property float $offTot
 * @property float $offRuis
 * @property int $CurrentLogAddress
 * @method void SetValueBoolean(string $Ident, bool $value)
 * @method void SetValueFloat(string $Ident, float $value)
 * @method void SetValueInteger(string $Ident, int $value)
 * @method void SetValueString(string $Ident, string $value)
 * @method int RegisterParent()
 * @method void RegisterProfileFloat(string $Name, string $Icon, string $Prefix, string $Suffix, float $MinValue, float $MaxValue, float $StepSize, int $Digits)
 * @method bool IORequestAction(string $Ident, mixed $Value)
 * @method void IOMessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data)
 */
class PlugwiseDevice extends IPSModuleStrict
{
    use \Plugwise\BufferHelper,
        \Plugwise\DebugHelper,
        \PlugwiseDevice\VariableHelper,
        \Plugwise\VariableProfileHelper,
        \Plugwise\InstanceStatus {
            \Plugwise\InstanceStatus::MessageSink as IOMessageSink;
            \Plugwise\InstanceStatus::RequestAction as IORequestAction;
        }

    /**
     * Create
     *
     * @return void
     */
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyString(\Plugwise\Node\Property::NodeMAC, '');
        $this->RegisterPropertyInteger(\Plugwise\Node\Property::Interval, 0);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showState, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showCurrent, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showAverage, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showOverall, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showFrequenz, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showTemperature, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showHumidity, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showTime, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showFirmware, true);
        $this->RegisterPropertyBoolean(\Plugwise\Node\Property::showHardware, true);
        $this->RegisterTimer(\Plugwise\Node\Timer::RequestState, 0, 'PLUGWISE_TimerEvent($_IPS["TARGET"]);');
        $this->RegisterTimer(\Plugwise\Node\Timer::SetTime, 0, 'PLUGWISE_SetTime($_IPS["TARGET"]);');
        $this->isCalib = false;
        $this->Type = \Plugwise\Plugwise_Typ::unknown;
        $this->gainA = 0;
        $this->gainB = 0;
        $this->offTot = 0;
        $this->offRuis = 0;
    }

    /**
     * ApplyChanges
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        $NodeMAC = $this->ReadPropertyString(\Plugwise\Node\Property::NodeMAC);
        if ($NodeMAC == '') {
            $Filter = '.*"NodeMAC":"999999999".*';
        } else {
            $Filter = '.*"NodeMAC":"' . $NodeMAC . '".*';
        }
        $this->SetReceiveDataFilter($Filter);
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->RegisterParent();

        //$this->RegisterProfileFloat('Plugwise.kwh', 'Electricity', '', ' kWh', 0, 0, 0, 3);
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showState)) {
            $this->UnregisterVariable('State');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showFrequenz)) {
            $this->UnregisterVariable('Frequenz');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showTime)) {
            $this->UnregisterVariable('Timestamp');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showCurrent)) {
            $this->UnregisterVariable('PowerCurrent');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showAverage)) {
            $this->UnregisterVariable('PowerAverage');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showOverall)) {
            $this->UnregisterVariable('ConsumptionCurrentHour');
            $this->UnregisterVariable('ConsumptionOverall');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showHardware)) {
            $this->UnregisterVariable('Hardware');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showFirmware)) {
            $this->UnregisterVariable('Firmware');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showTemperature)) {
            $this->UnregisterVariable('Temperature');
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showHumidity)) {
            $this->UnregisterVariable('Humidity');
        }
        $this->SetStatus(IS_ACTIVE);
        if ($this->HasActiveParent()) {
            $this->IOChangeState(IS_ACTIVE);
        } else {
            $this->SetTimerInterval(\Plugwise\Node\Timer::RequestState, 0);
            $this->SetTimerInterval(\Plugwise\Node\Timer::SetTime, 0);
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
        $this->SendDebug('GetConfigurationForm', \Plugwise\Plugwise_Typ::ToString($this->Type), 0);
        switch ($this->Type) {
            case \Plugwise\Plugwise_Typ::Cricle:
            case \Plugwise\Plugwise_Typ::CriclePlus:
                $Form['elements'][2]['visible'] = false;
                break;
            case \Plugwise\Plugwise_Typ::Scan:
            case \Plugwise\Plugwise_Typ::Switche:
                $Form['elements'][1]['visible'] = false;
                $Form['elements'][2]['visible'] = false;
                $Form['actions'][2]['visible'] = false;
                $Form['actions'][3]['visible'] = false;
                break;
            case \Plugwise\Plugwise_Typ::Sense:
                $Form['elements'][1]['visible'] = false;
                $Form['actions'][2]['visible'] = false;
                $Form['actions'][3]['visible'] = false;
                break;
        }
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
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
                $this->SwitchMode($Value);
                break;
            default:
                echo $this->Translate('Invalid Ident') . PHP_EOL;
                break;
        }
    }

    /**
     * TimerEvent
     *
     * @return void
     */
    public function TimerEvent(): void
    {
        if ($this->RequestState()) {
            if (($this->Type == \Plugwise\Plugwise_Typ::Cricle) || ($this->Type == \Plugwise\Plugwise_Typ::CriclePlus)) {
                $this->RequestEnergy();
            }
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
        if (!is_bool($Value)) {
            echo $this->Translate('Value must be boolean') . PHP_EOL;
            return false;
        }
        if (!$this->ReadPropertyBoolean(\Plugwise\Node\Property::showState)) {
            echo $this->Translate('Switch state not active in instance') . PHP_EOL;
            return false;
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::SwitchRequest, null, ($Value ? \Plugwise\Plugwise_Switch::ON : \Plugwise\Plugwise_Switch::OFF));
        $ret = $this->Send($PlugwiseData);
        if ($ret === false) {
            return false;
        }
        $Ok = (substr($ret->Data, 0, 4) == ($Value ? \Plugwise\Plugwise_AckMsg::SWITCHON : \Plugwise\Plugwise_AckMsg::SWITCHOFF));
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
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::InfoRequest);
        $Result = $this->Send($PlugwiseData);
        if ($Result == false || strlen($Result->Data) != 42) {
            return false;
        }
        if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showHardware)) {
            $this->RegisterVariableString(
                'Hardware',
                $this->Translate('Hardware'),
                []
            );
            $Hardware = sprintf('%s-%s-%s', substr($Result->Data, 20, 4), substr($Result->Data, 24, 4), substr($Result->Data, 28, 4));
            $this->SetValueString('Hardware', $Hardware);
        }
        if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showFirmware)) {
            $this->RegisterVariableInteger(
                'Firmware',
                $this->Translate('Firmware'),
                '~UnixTimestampDate'
                //[]
            );
            $Firmware = intval(hexdec(substr($Result->Data, 32, 8)));
            $this->SetValueInteger('Firmware', $Firmware);
        }
        $this->Type = \Plugwise\Plugwise_Typ::$Type[substr($Result->Data, 40, 2)];
        $this->SetSummary(\Plugwise\Plugwise_Typ::ToString($this->Type));
        $this->SendDebug('Type', \Plugwise\Plugwise_Typ::ToString($this->Type), 0);
        switch ($this->Type) {
            case \Plugwise\Plugwise_Typ::Cricle:
            case \Plugwise\Plugwise_Typ::CriclePlus:
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showState)) {
                    $this->RegisterVariableBoolean(
                        'State',
                        $this->Translate('State'),
                        '~Switch'
                        //[]
                    );
                    $this->EnableAction('State');
                    $Value = (substr($Result->Data, 16, 2) == \Plugwise\Plugwise_Switch::ON);
                    $this->SetValueBoolean('State', $Value);

                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showFrequenz)) {
                    $this->RegisterVariableFloat(
                        'Frequenz',
                        $this->Translate('Frequenz'),
                        '~Hertz'
                        //[]
                    );
                    $this->SetValueFloat('Frequenz', (float) \Plugwise\Plugwise_Switch::$Hertz[hexdec(substr($Result->Data, 18, 2))]);
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showTime)) {
                    $this->RegisterVariableInteger(
                        'Timestamp',
                        $this->Translate('Timestamp'),
                        '~UnixTimestamp'
                        //[]
                    );
                    $Timestamp = \Plugwise\Plugwise_Frame::Hex2Timestamp(substr($Result->Data, 0, 8));
                    $this->SetValueInteger('Timestamp', $Timestamp);
                    $this->SendDebug('Timestamp', $Timestamp, 0);
                    $this->SendDebug('Timestamp', gmdate('H:i:s d.m.Y', $Timestamp), 0);
                }
                $CurrentLogAddress = hexdec(substr($Result->Data, 8, 8));
                $this->CurrentLogAddress = $CurrentLogAddress;
                $this->SendDebug('CurrentLogAddress', dechex($CurrentLogAddress), 0);
                $this->SendDebug('CurrentLogAddress', $CurrentLogAddress, 0);
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showCurrent)) {
                    $this->RegisterVariableFloat(
                        'PowerCurrent',
                        $this->Translate('Power Current'),
                        '~Watt.3680'
                        //[]
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showAverage)) {
                    $this->RegisterVariableFloat(
                        'PowerAverage',
                        $this->Translate('Power Current'),
                        '~Watt.3680'
                        //[]
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showOverall)) {
                    $this->RegisterVariableFloat(
                        'ConsumptionCurrentHour',
                        $this->Translate('Consumption last hour'),
                        'Plugwise.kwh'
                        //[]
                    );
                    $this->RegisterVariableFloat(
                        'ConsumptionOverall',
                        $this->Translate('Consumption Overall'),
                        'Plugwise.kwh'
                        //[]
                    );
                }
                break;
            case \Plugwise\Plugwise_Typ::Sense:
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showTemperature)) {
                    $this->RegisterVariableFloat(
                        'Temperature',
                        $this->Translate('Temperature'),
                        '~Temperature'
                        //[]
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showHumidity)) {
                    $this->RegisterVariableFloat(
                        'Humidity',
                        $this->Translate('Humidity'),
                        '~Humidity.F'
                        //[]
                    );
                }
                break;
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
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::CalibrationRequest);
        $Result = $this->Send($PlugwiseData);
        if ($Result == false) {
            return false;
        }
        $this->isCalib = true;
        $Values = str_split($Result->Data, 8);
        $this->gainA = \Plugwise\Plugwise_Frame::Hex2Float($Values[0]);
        $this->gainB = \Plugwise\Plugwise_Frame::Hex2Float($Values[1]);
        $this->offTot = \Plugwise\Plugwise_Frame::Hex2Float($Values[2]);
        $this->offRuis = \Plugwise\Plugwise_Frame::Hex2Float($Values[3]);
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
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::PowerUsageRequest);
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
        if ($Pulses1Hex != 'FFFF' && $this->ReadPropertyBoolean(\Plugwise\Node\Property::showCurrent)) {
            $Pulses = \Plugwise\Plugwise_Frame::pulsesCorrection(
                $Pulses1Hex,
                1,
                $this->offRuis,
                $this->offTot,
                $this->gainA,
                $this->gainB
            );
            $Watt = \Plugwise\Plugwise_Frame::pulsesToWatt($Pulses);
            $this->SendDebug('PowerCurrent', $Watt, 0);
            $this->SetValueFloat('PowerCurrent', $Watt);
        }
        if ($Pulses2Hex != 'FFFF' && $this->ReadPropertyBoolean(\Plugwise\Node\Property::showAverage)) {
            $Pulses = \Plugwise\Plugwise_Frame::pulsesCorrection(
                $Pulses2Hex,
                8,
                $this->offRuis,
                $this->offTot,
                $this->gainA,
                $this->gainB
            );
            $Watt = \Plugwise\Plugwise_Frame::pulsesToWatt($Pulses);
            $this->SendDebug('PowerAverage', $Watt, 0);
            $this->SetValueFloat('PowerAverage', $Watt);
        }
        if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showOverall)) {
            $PulsesTotal = \Plugwise\Plugwise_Frame::pulsesCorrection(
                $PulsesTotalHex,
                3600,
                $this->offRuis,
                $this->offTot,
                $this->gainA,
                $this->gainB
            );
            if ($PulsesTotal < 0) {
                $PulsesTotal = 0;
            }
            $this->SendDebug('PulsesTotal', $PulsesTotal, 0);
            $WattTotal = \Plugwise\Plugwise_Frame::pulsesToKwh($PulsesTotal);
            $VidHour = @$this->GetIDForIdent('ConsumptionCurrentHour');
            if ($VidHour > 0) {
                $OldValueHour = GetValueFloat($VidHour);
            } else {
                $OldValueHour = 0;
            }
            $this->SendDebug('Old Consumption last hour', $OldValueHour, 0);
            $this->SendDebug('Consumption last hour', $WattTotal, 0);
            $this->SetValueFloat('ConsumptionCurrentHour', $WattTotal);
            $AddValueTotal = $WattTotal - $OldValueHour;
            if ($AddValueTotal < 0) {
                $AddValueTotal = $WattTotal;
            }
            $this->SendDebug('AddValueTotal', $AddValueTotal, 0);
            $VidOverall = @$this->GetIDForIdent('ConsumptionOverall');
            if ($VidOverall > 0) {
                $OldOverall = GetValueFloat($VidOverall);
            } else {
                $OldOverall = 0;
            }
            $this->SendDebug('OldOverall', $OldOverall, 0);
            $NewValueTotal = $OldOverall + $AddValueTotal;
            $this->SetValueFloat('ConsumptionOverall', $NewValueTotal);
            $this->SendDebug('NewValueTotal', $NewValueTotal, 0);
        }
        return true;
    }

    /**
     * SetTime
     *
     * @return bool
     */
    public function SetTime(): bool
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::ClockSetRequest, null, \Plugwise\Plugwise_Frame::Timestamp2Hex(time()));
        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            return false;
        }
        if (substr($Result->Data, 0, 4) != \Plugwise\Plugwise_AckMsg::SETTIMEACK) {
            trigger_error($this->Translate('Error on set time in Node'), E_USER_NOTICE);
            return false;
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
        $PlugwiseData = new \Plugwise\Plugwise_Frame($Data);
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
        if ($State == IS_ACTIVE) {
            if ($this->HasActiveParent()) {
                if ($this->RequestState()) {
                    $this->SetTime();
                    if (($this->Type == \Plugwise\Plugwise_Typ::Cricle) || ($this->Type == \Plugwise\Plugwise_Typ::CriclePlus)) {
                        $this->RequestCalibration();
                        $this->RequestEnergy();
                    }
                }
                if ($this->ReadPropertyInteger(\Plugwise\Node\Property::Interval) > 0) {
                    $this->SetTimerInterval(\Plugwise\Node\Timer::RequestState, $this->ReadPropertyInteger(\Plugwise\Node\Property::Interval) * 1000);
                }
                $this->SetTimerInterval(\Plugwise\Node\Timer::SetTime, 60 * 60 * 1000);
            }
        }
    }

    /**
     * Decode
     *
     * Dekodiert die empfangenen Events und Antworten.
     *
     * @param \Plugwise\Plugwise_Frame $PlugwiseData Der zu dekodierende Datensatz als Objekt.
     * @return void
     */
    private function Decode(\Plugwise\Plugwise_Frame $PlugwiseData): void
    {
        $this->SendDebug('EVENT', $PlugwiseData, 0);

        switch ($PlugwiseData->Command) {
            case \Plugwise\Plugwise_Command::PushButtonResponse: //switch ???
                $this->RegisterVariableBoolean('Switch', $this->Translate('Switch'));
                $this->SetValueBoolean('Switch', (substr($PlugwiseData->Data, 0, 2) == \Plugwise\Plugwise_Switch::ON));
                break;
            case \Plugwise\Plugwise_Command::KeyPressResponse: //switch
                $this->RegisterVariableBoolean('Switch1', $this->Translate('Switch 1'));
                $this->RegisterVariableBoolean('Switch2', $this->Translate('Switch 2'));
                $this->SetValueBoolean('Switch1', (substr($PlugwiseData->Data, 0, 2) == \Plugwise\Plugwise_Switch::ON));
                $this->SetValueBoolean('Switch2', (substr($PlugwiseData->Data, 2, 2) == \Plugwise\Plugwise_Switch::ON));
                break;
            case \Plugwise\Plugwise_Command::SensInfoResponse: //sens
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showTemperature)) {
                    $Temperature = (hexdec(substr($PlugwiseData->Data, 4, 4)) - 17473) / 372.90;
                    $this->SetValueFloat('Temperature', $Temperature);
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showHumidity)) {
                    $Humidity = (hexdec(substr($PlugwiseData->Data, 0, 4)) - 3145) / 524.30;
                    $this->SetValueFloat('Humidity', $Humidity);
                }
                break;
            default:
                break;
        }
    }

    /**
     * Send
     *
     * Konvertiert $Data zu einem JSONString und versendet diese an den Splitter.
     *
     * @param \Plugwise\Plugwise_Frame $PlugwiseData Zu versendende Daten.
     * @return \Plugwise\Plugwise_Frame|bool Objekt mit der Antwort. false im Fehlerfall.
     */
    private function Send(\Plugwise\Plugwise_Frame $PlugwiseData): bool|\Plugwise\Plugwise_Frame
    {
        $NodeMAC = trim($this->ReadPropertyString(\Plugwise\Node\Property::NodeMAC));
        if ($NodeMAC == '') {
            return false;
        }
        $this->SendDebug('Send', $PlugwiseData, 0);
        $PlugwiseData->NodeMAC = $NodeMAC;
        $JSONData = $PlugwiseData->ToJSONStringForSplitter();
        $ResultString = @$this->SendDataToParent($JSONData);
        $this->SendDebug('Send', $JSONData, 0);
        if ($ResultString == false) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            echo $this->Translate('Timeout error') . PHP_EOL;
            return false;
        }
        /** @var \Plugwise\Plugwise_Frame|bool $Result */
        $Result = @unserialize($ResultString);
        $this->SendDebug('Response', $Result, 0);
        if (($Result === null) || ($Result === false)) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            echo $this->Translate('Error on send data') . PHP_EOL;
            return false;
        }
        if ($Result->Data === '00E1') {
            echo $this->Translate('Node not reachable') . PHP_EOL;
            return false;
        }

        return $Result;
    }
}
