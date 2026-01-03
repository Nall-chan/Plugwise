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
 * @method void UnregisterProfile(string $Name)
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
        $this->RegisterPropertyInteger(\Plugwise\Node\Property::Interval, 5);
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
        $this->Type = \Plugwise\NodeTyp::unknown;
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
        $this->isCalib = false;
        $this->Type = \Plugwise\NodeTyp::unknown;
        $this->gainA = 0;
        $this->gainB = 0;
        $this->offTot = 0;
        $this->offRuis = 0;
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
        $this->RegisterVariableBoolean(
            'Reachable',
            $this->Translate('Reachable'),
            ['MIN'                 => 0,
                'DIGITS'           => 0,
                'MULTILINE'        => false,
                'ICON'             => 'Warning',
                'INTERVALS_ACTIVE' => true,
                'MAX'              => 1,
                'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                'PERCENTAGE'       => false,
                'OPTIONS'          => json_encode(
                    [
                        [
                            'Value'       => false,
                            'Caption'     => $this->Translate('Alert'),
                            'IconActive'  => false,
                            'IconValue'   => '',
                            'ColorActive' => true,
                            'ColorValue'  => 16711680,
                        ],
                        1 => [
                            'Value'       => true,
                            'Caption'     => 'OK',
                            'IconActive'  => false,
                            'IconValue'   => '',
                            'ColorActive' => true,
                            'ColorValue'  => -1,
                        ]]
                ),
                'PREFIX'           => '',
                'SUFFIX'           => '',
                'USAGE_TYPE'       => 0]
        );
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
        $this->UnregisterProfile('Plugwise.kwh');
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
            case \Plugwise\NodeTyp::Cricle:
            case \Plugwise\NodeTyp::CriclePlus:
                $Form['elements'][2]['visible'] = false;
                break;
            case \Plugwise\NodeTyp::Scan:
            case \Plugwise\NodeTyp::Switche:
                $Form['elements'][1]['visible'] = false;
                $Form['elements'][2]['visible'] = false;
                $Form['actions'][2]['visible'] = false;
                $Form['actions'][3]['visible'] = false;
                break;
            case \Plugwise\NodeTyp::Sense:
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
            if (($this->Type == \Plugwise\NodeTyp::Cricle) || ($this->Type == \Plugwise\NodeTyp::CriclePlus)) {
                $this->RequestEnergy();
            }
        }
    }

    /**
     * SwitchMode
     *
     * IPS-Instanz-Funktion 'SwitchStateMode'. Schaltet den Circle ein oder aus.
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
        $PlugwiseData = new \Plugwise\Frame(\Plugwise\Command::SwitchRequest, null, ($Value ? \Plugwise\SwitchState::ON : \Plugwise\SwitchState::OFF));
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
                [
                    'PRESENTATION'    => VARIABLE_PRESENTATION_DATE_TIME,
                    'TEMPLATE'        => VARIABLE_TEMPLATE_DATE
                ]
            );
            $Firmware = intval(hexdec(substr($Result->Data, 32, 8)));
            $this->SetValueInteger('Firmware', $Firmware);
        }
        $this->Type = \Plugwise\NodeTyp::$Type[substr($Result->Data, 40, 2)];
        $this->SetSummary(\Plugwise\NodeTyp::ToString($this->Type));
        $this->SendDebug('Type', \Plugwise\NodeTyp::ToString($this->Type), 0);
        switch ($this->Type) {
            case \Plugwise\NodeTyp::Cricle:
            case \Plugwise\NodeTyp::CriclePlus:
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showState)) {
                    $this->RegisterVariableBoolean(
                        'State',
                        $this->Translate('State'),
                        [
                            'PRESENTATION' => VARIABLE_PRESENTATION_SWITCH
                        ]
                    );
                    $this->EnableAction('State');
                    $Value = (substr($Result->Data, 16, 2) == \Plugwise\SwitchState::ON);
                    $this->SetValueBoolean('State', $Value);
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showFrequenz)) {
                    $this->RegisterVariableFloat(
                        'Frequenz',
                        $this->Translate('Frequenz'),
                        [
                            'MIN'              => 0,
                            'DIGITS'           => 2,
                            'MULTILINE'        => false,
                            'ICON'             => 'Electricity',
                            'MAX'              => 0,
                            'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                            'INTERVALS'        => '[]',
                            'INTERVALS_ACTIVE' => false,
                            'PERCENTAGE'       => false,
                            'PREFIX'           => '',
                            'SUFFIX'           => ' Hz',
                            'USAGE_TYPE'       => 0,
                        ]
                    );
                    $this->SetValueFloat('Frequenz', (float) \Plugwise\SwitchState::$Hertz[hexdec(substr($Result->Data, 18, 2))]);
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showTime)) {
                    $this->RegisterVariableInteger(
                        'Timestamp',
                        $this->Translate('Timestamp'),
                        [
                            'PRESENTATION'    => VARIABLE_PRESENTATION_DATE_TIME,
                            'TEMPLATE'        => VARIABLE_TEMPLATE_DATE_TIME
                        ]
                    );
                    $Timestamp = \Plugwise\Frame::Hex2Timestamp(substr($Result->Data, 0, 8));
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
                        $this->Translate('Power current'),
                        [
                            'PRESENTATION'      => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                            'ICON'              => 'bolt',
                            'DECIMAL_SEPARATOR' => 'Client',
                            'COLOR'             => -1,
                            'DISPLAY_TYPE'      => 0,
                            'CONTENT_COLOR'     => -1,
                            'DIGITS'            => 2,
                            'INTERVALS'         => json_encode(
                                [
                                    [
                                        'ColorDisplay'        => -1,
                                        'ContentColorDisplay' => -1,
                                        'IntervalMinValue'    => 0.001,
                                        'IntervalMaxValue'    => 1,
                                        'ConstantActive'      => false,
                                        'ConstantValue'       => '',
                                        'ConversionFactor'    => 0.001,
                                        'PrefixActive'        => false,
                                        'PrefixValue'         => '',
                                        'SuffixActive'        => true,
                                        'SuffixValue'         => ' mW',
                                        'DigitsActive'        => false,
                                        'DigitsValue'         => 0,
                                        'IconActive'          => false,
                                        'IconValue'           => '',
                                        'ColorActive'         => false,
                                        'ColorValue'          => -1,
                                        'ContentColorActive'  => false,
                                        'ContentColorValue'   => -1,
                                    ],
                                    [
                                        'ColorDisplay'        => -1,
                                        'ContentColorDisplay' => -1,
                                        'IntervalMinValue'    => 1000,
                                        'IntervalMaxValue'    => 999999,
                                        'ConstantActive'      => false,
                                        'ConstantValue'       => '',
                                        'ConversionFactor'    => 1000,
                                        'PrefixActive'        => false,
                                        'PrefixValue'         => '',
                                        'SuffixActive'        => true,
                                        'SuffixValue'         => ' kW',
                                        'DigitsActive'        => true,
                                        'DigitsValue'         => 2,
                                        'IconActive'          => false,
                                        'IconValue'           => '',
                                        'ColorActive'         => false,
                                        'ColorValue'          => -1,
                                        'ContentColorActive'  => false,
                                        'ContentColorValue'   => -1,
                                    ]
                                ]
                            ),
                            'INTERVALS_ACTIVE'    => true,
                            'MAX'                 => 0,
                            'MIN'                 => 0,
                            'PERCENTAGE'          => false,
                            'PREFIX'              => '',
                            'PREVIEW_STYLE'       => 1,
                            'SHOW_PREVIEW'        => true,
                            'SUFFIX'              => ' W',
                            'THOUSANDS_SEPARATOR' => '',
                            'USAGE_TYPE'          => 0,

                        ]
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showAverage)) {
                    $this->RegisterVariableFloat(
                        'PowerAverage',
                        $this->Translate('Power average'),
                        [
                            'PRESENTATION'      => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                            'ICON'              => 'bolt',
                            'DECIMAL_SEPARATOR' => 'Client',
                            'COLOR'             => -1,
                            'DISPLAY_TYPE'      => 0,
                            'CONTENT_COLOR'     => -1,
                            'DIGITS'            => 2,
                            'INTERVALS'         => json_encode(
                                [
                                    [
                                        'ColorDisplay'        => -1,
                                        'ContentColorDisplay' => -1,
                                        'IntervalMinValue'    => 0.001,
                                        'IntervalMaxValue'    => 1,
                                        'ConstantActive'      => false,
                                        'ConstantValue'       => '',
                                        'ConversionFactor'    => 0.001,
                                        'PrefixActive'        => false,
                                        'PrefixValue'         => '',
                                        'SuffixActive'        => true,
                                        'SuffixValue'         => ' mW',
                                        'DigitsActive'        => false,
                                        'DigitsValue'         => 0,
                                        'IconActive'          => false,
                                        'IconValue'           => '',
                                        'ColorActive'         => false,
                                        'ColorValue'          => -1,
                                        'ContentColorActive'  => false,
                                        'ContentColorValue'   => -1,
                                    ],
                                    [
                                        'ColorDisplay'        => -1,
                                        'ContentColorDisplay' => -1,
                                        'IntervalMinValue'    => 1000,
                                        'IntervalMaxValue'    => 999999,
                                        'ConstantActive'      => false,
                                        'ConstantValue'       => '',
                                        'ConversionFactor'    => 1000,
                                        'PrefixActive'        => false,
                                        'PrefixValue'         => '',
                                        'SuffixActive'        => true,
                                        'SuffixValue'         => ' kW',
                                        'DigitsActive'        => true,
                                        'DigitsValue'         => 2,
                                        'IconActive'          => false,
                                        'IconValue'           => '',
                                        'ColorActive'         => false,
                                        'ColorValue'          => -1,
                                        'ContentColorActive'  => false,
                                        'ContentColorValue'   => -1,
                                    ]
                                ]
                            ),
                            'INTERVALS_ACTIVE'    => true,
                            'MAX'                 => 0,
                            'MIN'                 => 0,
                            'PERCENTAGE'          => false,
                            'PREFIX'              => '',
                            'PREVIEW_STYLE'       => 1,
                            'SHOW_PREVIEW'        => true,
                            'SUFFIX'              => ' W',
                            'THOUSANDS_SEPARATOR' => '',
                            'USAGE_TYPE'          => 0,

                        ]
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showOverall)) {
                    $this->RegisterVariableFloat(
                        'ConsumptionOverall',
                        $this->Translate('Consumption overall'),
                        [
                            'ICON'                => 'bolt',
                            'DECIMAL_SEPARATOR'   => 'Client',
                            'COLOR'               => -1,
                            'DISPLAY_TYPE'        => 0,
                            'CONTENT_COLOR'       => -1,
                            'DIGITS'              => 2,
                            'INTERVALS'           => json_encode([
                                [
                                    'ColorDisplay'        => -1,
                                    'ContentColorDisplay' => -1,
                                    'IntervalMinValue'    => 0.001,
                                    'IntervalMaxValue'    => 1,
                                    'ConstantActive'      => false,
                                    'ConstantValue'       => '',
                                    'ConversionFactor'    => 0.001,
                                    'PrefixActive'        => false,
                                    'PrefixValue'         => '',
                                    'SuffixActive'        => true,
                                    'SuffixValue'         => ' Wh',
                                    'DigitsActive'        => false,
                                    'DigitsValue'         => 2,
                                    'IconActive'          => false,
                                    'IconValue'           => '',
                                    'ColorActive'         => false,
                                    'ColorValue'          => -1,
                                    'ContentColorActive'  => false,
                                    'ContentColorValue'   => -1,
                                ],

                                [
                                    'ColorDisplay'        => -1,
                                    'ContentColorDisplay' => -1,
                                    'IntervalMinValue'    => 1000,
                                    'IntervalMaxValue'    => 999999,
                                    'ConstantActive'      => false,
                                    'ConstantValue'       => '',
                                    'ConversionFactor'    => 1000,
                                    'PrefixActive'        => false,
                                    'PrefixValue'         => '',
                                    'SuffixActive'        => true,
                                    'SuffixValue'         => 'MWh',
                                    'DigitsActive'        => true,
                                    'DigitsValue'         => 2,
                                    'IconActive'          => false,
                                    'IconValue'           => '',
                                    'ColorActive'         => false,
                                    'ColorValue'          => -1,
                                    'ContentColorActive'  => false,
                                    'ContentColorValue'   => -1,
                                ],
                            ]),
                            'INTERVALS_ACTIVE'    => true,
                            'MAX'                 => 0,
                            'PRESENTATION'        => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                            'MIN'                 => 0,
                            'PERCENTAGE'          => false,
                            'PREFIX'              => '',
                            'PREVIEW_STYLE'       => 1,
                            'SHOW_PREVIEW'        => true,
                            'SUFFIX'              => ' kWh',
                            'THOUSANDS_SEPARATOR' => '',
                            'USAGE_TYPE'          => 0,
                        ]
                    );
                }
                break;
            case \Plugwise\NodeTyp::Sense:
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showTemperature)) {
                    $this->RegisterVariableFloat(
                        'Temperature',
                        $this->Translate('Temperature'),
                        [
                            'MIN'              => -30,
                            'DIGITS'           => 1,
                            'MULTILINE'        => false,
                            'ICON'             => 'Temperature',
                            'MAX'              => 70,
                            'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                            'INTERVALS'        => '[]',
                            'INTERVALS_ACTIVE' => false,
                            'PERCENTAGE'       => false,
                            'PREFIX'           => '',
                            'SUFFIX'           => ' °C',
                            'USAGE_TYPE'       => 1,
                        ]
                    );
                }
                if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showHumidity)) {
                    $this->RegisterVariableFloat(
                        'Humidity',
                        $this->Translate('Humidity'),
                        [
                            'MIN'              => 0,
                            'DIGITS'           => 1,
                            'MULTILINE'        => false,
                            'ICON'             => 'Gauge',
                            'MAX'              => 100,
                            'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                            'INTERVALS'        => '[]',
                            'INTERVALS_ACTIVE' => false,
                            'PERCENTAGE'       => true,
                            'PREFIX'           => '',
                            'SUFFIX'           => ' %',
                            'USAGE_TYPE'       => 0,
                        ]
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
        $PlugwiseData = new \Plugwise\Frame(\Plugwise\Command::CalibrationRequest);
        $Result = $this->Send($PlugwiseData);
        if ($Result == false) {
            return false;
        }
        $this->isCalib = true;
        $Values = str_split($Result->Data, 8);
        $this->gainA = \Plugwise\Frame::Hex2Float($Values[0]);
        $this->gainB = \Plugwise\Frame::Hex2Float($Values[1]);
        $this->offTot = \Plugwise\Frame::Hex2Float($Values[2]);
        $this->offRuis = \Plugwise\Frame::Hex2Float($Values[3]);
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
        if ($Pulses1Hex != 'FFFF' && $this->ReadPropertyBoolean(\Plugwise\Node\Property::showCurrent)) {
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
        if ($Pulses2Hex != 'FFFF' && $this->ReadPropertyBoolean(\Plugwise\Node\Property::showAverage)) {
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
        if ($this->ReadPropertyBoolean(\Plugwise\Node\Property::showOverall)) {
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
     * SetTime
     *
     * @return bool
     */
    public function SetTime(): bool
    {
        $PlugwiseData = new \Plugwise\Frame(\Plugwise\Command::ClockSetRequest, null, \Plugwise\Frame::Timestamp2Hex(time()));
        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            return false;
        }
        if (substr($Result->Data, 0, 4) != \Plugwise\AckMsg::SETTIMEACK) {
            trigger_error($this->Translate('Error on set time in Node'), E_USER_NOTICE);
            return false;
        }
        return true;
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
                    if (($this->Type == \Plugwise\NodeTyp::Cricle) || ($this->Type == \Plugwise\NodeTyp::CriclePlus)) {
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
     * @param \Plugwise\Frame $PlugwiseData Der zu dekodierende Datensatz als Objekt.
     * @return void
     */
    private function Decode(\Plugwise\Frame $PlugwiseData): void
    {
        $this->SendDebug('EVENT', $PlugwiseData, 0);

        switch ($PlugwiseData->Command) {
            case \Plugwise\Command::PushButtonResponse: //switch ???
                $this->RegisterVariableBoolean('Switch', $this->Translate('Switch'), []);
                $this->SetValueBoolean('Switch', (substr($PlugwiseData->Data, 0, 2) == \Plugwise\SwitchState::ON));
                break;
            case \Plugwise\Command::KeyPressResponse: //switch
                $this->RegisterVariableBoolean('Switch1', $this->Translate('Switch 1'), []);
                $this->RegisterVariableBoolean('Switch2', $this->Translate('Switch 2'), []);
                $this->SetValueBoolean('Switch1', (substr($PlugwiseData->Data, 0, 2) == \Plugwise\SwitchState::ON));
                $this->SetValueBoolean('Switch2', (substr($PlugwiseData->Data, 2, 2) == \Plugwise\SwitchState::ON));
                break;
            case \Plugwise\Command::SensInfoResponse: //sens
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
     * @param \Plugwise\Frame $PlugwiseData Zu versendende Daten.
     * @return \Plugwise\Frame|bool Objekt mit der Antwort. false im Fehlerfall.
     */
    private function Send(\Plugwise\Frame $PlugwiseData): bool|\Plugwise\Frame
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
        /** @var \Plugwise\Frame|bool $Result */
        $Result = @unserialize($ResultString);
        $this->SendDebug('Response', $Result, 0);
        if (($Result === null) || ($Result === false)) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            echo $this->Translate('Error on send data') . PHP_EOL;
            return false;
        }
        if ($Result->Data === \Plugwise\AckMsg::OUTOFRANGE) {
            $this->SetValueBoolean('Reachable', false);
            echo $this->Translate('Node not reachable') . PHP_EOL;
            return false;
        }
        $this->SetValueBoolean('Reachable', true);
        return $Result;
    }
}
