<?php

declare(strict_types=1);

/**
 * @package       Plugwise
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2017-2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       2.10
 *
 */

namespace {

    eval('declare(strict_types=1);namespace Plugwise {?>' . file_get_contents(__DIR__ . '/helper/BufferHelper.php') . '}');
    eval('declare(strict_types=1);namespace Plugwise {?>' . file_get_contents(__DIR__ . '/helper/SemaphoreHelper.php') . '}');
    eval('declare(strict_types=1);namespace Plugwise {?>' . file_get_contents(__DIR__ . '/helper/ParentIOHelper.php') . '}');
    eval('declare(strict_types=1);namespace Plugwise {?>' . file_get_contents(__DIR__ . '/helper/VariableProfileHelper.php') . '}');
}

namespace Plugwise\Node{
    class Property
    {
        public const NodeMAC = 'NodeMAC';
        public const Interval = 'Interval';
        public const ShowState = 'showState';
        public const ShowCurrent = 'showCurrent';
        public const ShowAverage = 'showAverage';
        public const ShowOverall = 'showOverall';
        public const ShowFeedIn = 'showFeedIn';
        public const ShowFrequenz = 'showFrequenz';
        public const ShowTemperature = 'showTemperature';
        public const ShowHumidity = 'showHumidity';
        public const ShowTime = 'showTime';
        public const ShowFirmware = 'showFirmware';
        public const ShowHardware = 'showHardware';
    }
    class Timer
    {
        public const RequestState = 'RequestState';
        public const SetTime = 'SetTime';
    }
}

namespace Plugwise\Network{
    class Timer
    {
        public const SearchNodes = 'SearchNodes';
        public const DisableNetworkJoining = 'DisableNetworkJoining';
        public const SyncTime = 'SyncTime';
    }
}

namespace Plugwise{
    class GUID
    {
        public const SerialPort = '{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}';
        public const Network = '{7C20491F-F145-4F1C-A69C-AAE1F60F5BD5}';
        public const Node = '{5FD73328-68F3-4047-B678-E385C2E31962}';
    }

    /**
     * NetworkState
     */
    class NetworkState
    {
        public const StickNotFound = 0;
        public const CirclePlusMissing = 1;
        public const CirclePlusOffline = 2;
        public const SearchingCirclePlus = 3;
        public const ParingCirclePlus = 4;
        public const SearchingNodes = 5;
        public const Online = 7;
        public const OnlineJoining = 8;

        /**
         * ToString
         *
         * @param  int $NetworkState
         * @return string
         */
        public static function ToString(int $NetworkState): string
        {
            switch ($NetworkState) {
                case self::StickNotFound:
                    return 'Stick not found';
                case self::CirclePlusMissing:
                    return 'Circle+ missing / No PAN ID';
                case self::CirclePlusOffline:
                    return 'Circle+ offline';
                case self::SearchingCirclePlus:
                    return 'Search for Circle+';
                case self::ParingCirclePlus:
                    return 'Ongoing paring with Circle+';
                case self::SearchingNodes:
                    return 'Ongoing network discovery';
                case self::Online:
                    return 'Online';
                case self::OnlineJoining:
                    return 'Online, joining possible';
                default:
                    return (string) $NetworkState;
            }
        }
    }

    /**
     * SwitchState
     */
    class SwitchState
    {
        public const On = '01';
        public const Off = '00';

        public static $Hertz = [
            133 => 50,
            197 => 60
        ];

        /**
         * FromGroupData
         *
         * Dekodiert die Nutzdaten der NodeSwitchGroupResponse (0056): Gruppe (1 Byte) und Zustand (1 Byte).
         * Alle batteriebetriebenen Nodes (Scan, Sense, Switch) melden damit, dass eine Schaltbedingung
         * für eine Gruppe erfüllt (Zustand ungleich 0) oder beendet (0) ist. Der Scan meldet so Bewegung ein/aus.
         *
         * @param  string $Data Hex-String mit 4 Zeichen.
         * @return array|null ['Group' => int, 'State' => bool] oder null, wenn die Daten nicht passen.
         */
        public static function FromGroupData(string $Data): ?array
        {
            if (preg_match('/^[0-9A-Fa-f]{4}/', $Data) !== 1) {
                return null;
            }
            return [
                'Group' => Frame::HexToInt(substr($Data, 0, 2)),
                'State' => Frame::HexToInt(substr($Data, 2, 2)) != 0
            ];
        }

        /**
         * ToHertz
         *
         * Liefert die Netzfrequenz zum Code des Nodes.
         *
         * @param  int $Code
         * @return float|null null, wenn der Code unbekannt ist.
         */
        public static function ToHertz(int $Code): ?float
        {
            return isset(self::$Hertz[$Code]) ? (float) self::$Hertz[$Code] : null;
        }

        /**
         * ToString
         *
         * @param  string $SwitchState
         * @return string
         */
        public static function ToString(string $SwitchState): string
        {
            switch ($SwitchState) {
                case self::On:
                    return 'ON';
                case self::Off:
                    return 'OFF';
                default:
                    return '????????????';
            }
        }
    }

    /**
     * AckMsg
     */
    class AckMsg
    {
        public const ACK = '00C1';
        public const NACK = '00C2';
        public const UNKNOWN = '00C3';          //	"Command not allowed"
        public const SWITCHON = '00D8';
        public const JOININGENABLE = '00D9';   //	"Allow nodes to join ACK1"
        public const JOININGDISABLE = '00DD';  //	"Allow nodes to join ACK0"
        public const SWITCHOFF = '00DE';
        public const SUCCESSFUL = '00DF';      //	"Set RTC-Data ACK"
        //                 "00E7";      //	"Set RTC-Data NACK"
        public const OUTOFRANGE = '00E1';
        public const DISCONNECTED = '00F2';    //	"Reply role changed OK"
        //                  "00F3";     //	"Reply role changed NOK"
        public const CONNECTED = '00F4';       //	"Set handle on"
        //                  "00F5";     //	"Set handle off"
        //                  "00F9";     //	"Clear group MAC-Table"
        //          	"00FA";     //	"Fill Switch-schedule"
        //  	 	"00F7";     //	"Request self-removal from network"
        //          	"00F1";     //	"Set broadcast-time interval"
        //                  "00E6";     //	"Set PN"
        //           	"00F8";     //	"Set PowerRecording"
        //                  "00BE";     //	"Set scan-params ACK"
        //  	 	"00BF";     //	"Set scan-params NACK"
        //  	 	"00B5";     //	"Set sense-boundaries ACK"
        //  	 	"00B6";     //	"Set sense-boundaries NACK"
        //  	 	"00B3";     //	"Set sense-interval ACK"
        //  	 	"00B4";     //	"Set sense-interval NACK"
        //  	 	"00F6";     //	"Set sleep-behavior"
        //  	 	"00E5";     //	"Activate Switch-schedule on"
        //  	 	"00E4";     //	"Activate Switch-schedule off"
        //  	 	"00C8";     //	"Bootload aborted"
        //  	 	"00C9";     //	"Bootload done"
        //  	 	"00D5";     //	"Cancel read Powermeter-Info Logdata"
        //  	 	"00C4";     //	"Cannot join network"
        //  	 	"00D1";     //	"Done reading Powermeter-Info Logdata"
        //  	 	"00C0";     //	"Ember stack error
        //  	 	"00C5";     //	"Exceeding TableIndex"
        //  	 	"00CF";     //	"Flash erased"
        //  	 	"00C6";     //	"Flash error"
        //  	 	"00ED";     //	"Group-MAC added"
        //  	 	"00EF";     //	"Group-MAC not added"
        //  	 	"00F0";     //	"Group-MAC not removed"
        //  	 	"00EE";     //	"Group-MAC removed"
        //  	 	"00E8";     //	"Image activate ACK"
        //  	 	"00CC";     //	"Image check timeout"
        //  	 	"00CB";     //	"Image invalid"
        //  	 	"00CA";     //	"Image valid"
        //  	 	"00C7";     //	"Node-change accepted"
        //  	 	"00CD";     //	"Ping timeout 1sec"
        //  	 	"00EB";     //	"Pingrun busy"
        //  	 	"00EC";     //	"Pingrun finished"
        //  	 	"00CE";     //	"Public network-info complete"
        //  	 	"00D0";     //	"Remote flash erased"
        //  	 	"00E0";     //	"Send switchblock NACK"
        //  	 	"00DA";     //	"Send calib-params ACK"
        //  	 	"00E2";     //	"Set relais denied"
        public const SETTIMEACK = '00D7';     //	"Set year, month and flashadress DONE"

        //  	 	"00BD";     //	"Start Light-Calibration started"
        //  	 	"00E9";     //	"Start Pingrun ACK"
        //  	 	"00EA";     //	"Stop Pingrun ACK"
        //  	 	"00DC";     //	"Synchronize NC ACK"
        //  	 	"00D6";     //	"Timeout Powermeter Logdata"

        /**
         * ToString
         *
         * @param  string $AckMsg
         * @return string
         */
        public static function ToString(string $AckMsg): string
        {
            switch ($AckMsg) {
                case self::ACK:
                    return 'ACK';
                case self::NACK:
                    return 'NACK';
                case self::UNKNOWN:
                    return 'UNKNOWN';
                case self::SWITCHON:
                    return 'SWITCH ON';
                case self::JOININGENABLE:
                    return 'JOINING ENABLE';
                case self::JOININGDISABLE:
                    return 'JOINING DISABLE';
                case self::SWITCHOFF:
                    return 'SWITCH OFF';
                case self::SUCCESSFUL:
                    return 'SUCCESSFUL';
                case self::OUTOFRANGE:
                    return 'OUT OF RANGE';
                case self::DISCONNECTED:
                    return 'DISCONNECTED';
                case self::CONNECTED:
                    return 'CONNECTED';
                case self::SETTIMEACK:
                    return 'SET TIME ACK';
                default:
                    return $AckMsg . ' = ????????????';
            }
        }
    }

    /**
     * Command
     */
    class Command
    {
        /**
         * Response from stick after request
         */
        public const AckMsgResponse = '0000';

        /**
         * Query any presence off networks. Maybe intended to find a Circle+ from the Stick
         */
        public const QueryCirclePlusRequest = '0001';
        public const QueryCirclePlusResponse = '0002';
        public const QueryCirclePlusResponseEnd = '0003';

        /**
         * Request connection to the network. Maybe intended to connect a Circle+ to the Stick
         */
        public const ConnectCirclePlusRequest = '0004';
        public const ConnectCirclePlusResponse = '0005';

        /**
         * Broadcast from factory-default nodes
         */
        public const AdvertiseNodeResponse = '0006';

        /**
         * Send Join nodes request to add a new node to the network
         */
        public const JoinNodeRequest = '0007';

        /*
         * Send a flag which enables or disables joining nodes request
         */
        public const EnableJoiningRequest = '0008';

        /**
         * Send preset circle request
         */
        public const ResetRequest = '0009';

        /**
         * message for that initializes the Stick
         */
        public const StickStatusRequest = '000A';

        /**
         * Send ping to node
         */
        public const Ping = '000D';
        public const PingResponse = '000E';

        /**
         * Status from stick
         */
        public const StickStatusResponse = '0011';

        /**
         * Request for power usage
         */
        public const PowerUsageRequest = '0012';

        /**
         * returns power usage as impulse counters for several different TimeFrames
         */
        public const PowerUsageResponse = '0013';

        /**
         * Set time on circle+
         */
        public const ClockSetRequest = '0016'; // an Circle

        /**
         * switches Plug on or off
         */
        public const SwitchRequest = '0017';

        /**
         * Send populate request
         */
        public const AssociatedNodesRequest = '0018';

        /**
         * AssociatedNodes response
         */
        public const AssociatedNodesResponse = '0019';

        /**
         * Request remove node from network
         */
        public const RemoveNodeRequest = '001C';

        /**
         * RemoveNode response
         */
        public const RemoveNodeResponse = '001D';

        /**
         * Info request and response
         */
        public const InfoRequest = '0023';
        public const InfoResponse = '0024';

        /**
         * Calibration request and response
         */
        public const CalibrationRequest = '0026';
        public const CalibrationResponse = '0027';

        /**
         * DateTime request and response
         */
        public const SetDateTimeRequest = '0028';
        public const DateTimeInfoRequest = '0029';
        public const DateTimeInfoResponse = '003A';

        /**
         * Send chunk of On/Off/StandbyKiller Schedule to Stick
         */
        public const PrepareScheduleRequest = '003B';

        /**
         * Send chunk of  On/Off/StandbyKiller Schedule to Circle(+)
         */
        public const SendScheduleRequest = '003C';
        public const SendScheduleResponse = '003D';

        /**
         * Clock request and response
         */
        public const ClockInfoRequest = '003E';
        public const ClockInfoResponse = '003F';

        /**
         * switches Schedule on or off
         */
        public const EnableScheduleRequest = '0040';

        /**
         * Request power usage historical data
         */
        public const PowerBufferRequest = '0048';

        /**
         * returns information about historical power usage
         * each response contains 4 log buffers and each log buffer contains data for 1 hour
         */
        public const PowerBufferResponse = '0049';

        /**
         * Eventuell Anlernmodus Circle+ ???
         */
        public const GetCirclePlus1 = '004A';

        /**
         * Verbundenen Circle+ anfordern.
         */
        public const GetConnectedCirclePlus = '004E'; // Antwort als ACK mit Connected 00F4

        /**
         * Ein batteriebetriebener Node (Scan, Sense, Switch) ist aufgewacht, Nutzdaten: Grund (1 Byte), siehe NodeAwake.
         */
        public const NodeAwakeResponse = '004F';

        /**
         * Schaltbedingung einer Gruppe, Nutzdaten: Gruppe (1 Byte) und Zustand (1 Byte).
         * Scan: Bewegung, Switch: Taste, Sense: Hysterese von Feuchte und Temperatur.
         */
        public const NodeSwitchGroupResponse = '0056';

        /**
         * ???
         */
        public const LogIntervalRequest = '0057';

        /**
         * ???
         */
        public const ClearGroupMacRequest = '0058';

        /**
         * Send chunk of  On/Off/StandbyKiller Schedule to Circle(+)
         */
        public const SetScheduleValueRequest = '0059';

        /**
         * ???
         */
        public const FeatureSetRequest = '005F';

        /**
         * returns feature set of modules
         */
        public const FeatureSetResponse = '0060';

        /**
         * Broadcast when node join network
         */
        public const AckAssociationResponse = '0061';

        /**
         * Sens values
         */
        public const SensInfoResponse = '0105';

        /**
         * ToString
         *
         * @param  string $Command
         * @return string
         */
        public static function ToString(string $Command): string
        {
            switch ($Command) {
                case self::AckMsgResponse:
                    return 'AckMsgResponse';
                case self::QueryCirclePlusRequest:
                    return 'QueryCirclePlusRequest';
                case self::QueryCirclePlusResponse:
                    return 'QueryCirclePlusResponse';
                case self::QueryCirclePlusResponseEnd:
                    return 'QueryCirclePlusResponseEnd';
                case self::ConnectCirclePlusRequest:
                    return 'ConnectCirclePlusRequest';
                case self::ConnectCirclePlusResponse:
                    return 'ConnectCirclePlusResponse';
                case self::AdvertiseNodeResponse:
                    return 'AdvertiseNodeResponse';
                case self::JoinNodeRequest:
                    return 'JoinNodeRequest';
                case self::EnableJoiningRequest:
                    return 'EnableJoiningRequest';
                case self::ResetRequest:
                    return 'ResetRequest';
                case self::StickStatusRequest:
                    return 'StickStatusRequest';
                case self::Ping:
                    return 'Ping';
                case self::PingResponse:
                    return 'PingResponse';
                case self::StickStatusResponse:
                    return 'StickStatusResponse';
                case self::PowerUsageRequest:
                    return 'PowerUsageRequest';
                case self::PowerUsageResponse:
                    return 'PowerUsageResponse';
                case self::ClockSetRequest:
                    return 'ClockSetRequest';
                case self::SwitchRequest:
                    return 'SwitchRequest';
                case self::AssociatedNodesRequest:
                    return 'AssociatedNodesRequest';
                case self::AssociatedNodesResponse:
                    return 'AssociatedNodesResponse';
                case self::RemoveNodeRequest:
                    return 'RemoveNodeRequest';
                case self::RemoveNodeResponse:
                    return 'RemoveNodeResponse';
                case self::InfoRequest:
                    return 'InfoRequest';
                case self::InfoResponse:
                    return 'InfoResponse';
                case self::CalibrationRequest:
                    return 'CalibrationRequest';
                case self::CalibrationResponse:
                    return 'CalibrationResponse';
                case self::SetDateTimeRequest:
                    return 'SetDateTimeRequest';
                case self::DateTimeInfoRequest:
                    return 'DateTimeInfoRequest';
                case self::DateTimeInfoResponse:
                    return 'DateTimeInfoResponse';
                case self::PrepareScheduleRequest:
                    return 'PrepareScheduleRequest';
                case self::SendScheduleRequest:
                    return 'SendScheduleRequest';
                case self::SendScheduleResponse:
                    return 'SendScheduleResponse';
                case self::ClockInfoRequest:
                    return 'ClockInfoRequest';
                case self::ClockInfoResponse:
                    return 'ClockInfoResponse';
                case self::EnableScheduleRequest:
                    return 'EnableScheduleRequest';
                case self::PowerBufferRequest:
                    return 'PowerBufferRequest';
                case self::PowerBufferResponse:
                    return 'PowerBufferResponse';
                case self::GetCirclePlus1:
                    return 'GetCirclePlus1';
                case self::GetConnectedCirclePlus:
                    return 'GetConnectedCirclePlus';
                case self::SensInfoResponse:
                    return 'SensInfoResponse';
                case self::NodeAwakeResponse:
                    return 'NodeAwakeResponse';
                case self::NodeSwitchGroupResponse:
                    return 'NodeSwitchGroupResponse';
                case self::LogIntervalRequest:
                    return 'LogIntervalRequest';
                case self::ClearGroupMacRequest:
                    return 'ClearGroupMacRequest';
                case self::SetScheduleValueRequest:
                    return 'SetScheduleValueRequest';
                case self::FeatureSetRequest:
                    return 'FeatureSetRequest';
                case self::FeatureSetResponse:
                    return 'FeatureSetResponse';
                case self::AckAssociationResponse:
                    return 'AckAssociationResponse';
                default:
                    return $Command . ' ????';
            }
        }
    }

    /**
     * NodeAwake
     *
     * Grund, warum ein batteriebetriebener Node aufgewacht ist (NodeAwakeResponse 004F, 1 Byte).
     */
    class NodeAwake
    {
        public const Maintenance = 0;
        public const First = 1;
        public const Startup = 2;
        public const State = 3;
        public const Button = 5;

        /**
         * ToString
         *
         * @param  int $AwakeType
         * @return string
         */
        public static function ToString(int $AwakeType): string
        {
            switch ($AwakeType) {
                case self::Maintenance:
                    return 'Maintenance';
                case self::First:
                    return 'First join';
                case self::Startup:
                    return 'Startup';
                case self::State:
                    return 'Report state';
                case self::Button:
                    return 'Button pressed';
                default:
                    return (string) $AwakeType;
            }
        }
    }

    /**
     * NodeTyp
     */
    class NodeTyp
    {
        public const Unknown = 0;
        public const Circle = 1;
        public const Switch = 2; //No typo, switch is reserved by PHP ;)
        public const Sense = 3;
        public const Scan = 4;
        public const CirclePlus = 5;
        public const Stealth = 6;
        /**
         * Typ-Codes aus der InfoResponse.
         */
        public static $Type = [
            //0    => self::Circle,
            '00' => self::CirclePlus,
            '01' => self::CirclePlus,
            '02' => self::Circle,
            '03' => self::Switch,
            '04' => self::Switch,
            '05' => self::Sense,
            '06' => self::Scan,
            '09' => self::Stealth,
        ];

        /**
         * FromHex
         *
         * Liefert den Node-Typ zum Typ-Code aus der InfoResponse.
         * Unbekannte Codes (z.B. Stealth) ergeben NodeTyp::Unknown.
         *
         * @param  string $TypeCode Typ-Code als Hex-String (2 Zeichen).
         * @return int
         */
        public static function FromHex(string $TypeCode): int
        {
            return self::$Type[strtoupper($TypeCode)] ?? self::Unknown;
        }

        /**
         * ToString
         *
         * @param  int $NodeType
         * @return string
         */
        public static function ToString(int $NodeType): string
        {
            switch ($NodeType) {
                case self::CirclePlus:
                    return 'Circle+';
                case self::Circle:
                    return 'Circle';
                case self::Switch:
                    return 'Switch';
                case self::Sense:
                    return 'Sense';
                case self::Scan:
                    return 'Scan';
                case self::Stealth:
                    return 'Stealth';
                default:
                    return (string) $NodeType;
            }
        }
    }

    /**
     * Enthält einen Plugwise Datenpaket.
     *
     * @package       Plugwise
     * @author        Michael Tröger <nall4chan@gmail.com>
     * @copyright     2017-2026 Michael Tröger
     * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
     * @version       2.10
     *
     *
     * @property string $Command Kommando
     * @property int $FrameID Frame-ID
     * @property string $NodeMAC ID des Circle
     * @property string $Data Payload
     * @property ?bool $Checksum Checksumme
     */
    class Frame
    {
        /**
         * Platzhalter für die MAC des Circle+ in Nachrichten der Instanzen an den Splitter.
         * Der Splitter ersetzt ihn beim Weiterleiten durch die echte MAC (siehe Konstruktor).
         * Hat bewusst nur 8 Zeichen und kann daher nie mit einer echten MAC (16 Zeichen) verwechselt werden.
         */
        public const CirclePlusPlaceholder = 'FFFFFFFF';

        /**
         * Kommando
         *
         * @var string
         */
        public string $Command = '';

        /**
         * Frame-ID
         *
         * @var int
         */
        public int $FrameID = -1;

        /**
         * ID des Circle
         *
         * @var string
         */
        public string $NodeMAC = '';

        /**
         * Payload
         *
         * @var string
         */
        public string $Data = '';

        /**
         * Checksumme
         *
         * @var bool|null null, solange sie nicht geprüft wurde.
         */
        public ?bool $Checksum = null;

        /**
         * Erstellt ein Frame Objekt.
         *
         *
         * @param string $Command [optional] Kommando
         * @param string $NodeMAC [optional] ID des Circle
         * @param string $Data [optional] Payload
         * @return Frame
         */
        public function __construct($Command = null, $NodeMAC = null, $Data = null)
        {
            if (is_object($Command)) {
                $this->Command = hex2bin($Command->Command);

                $NodeMAC2 = hex2bin($Command->NodeMAC);
                if ($NodeMAC2 == self::CirclePlusPlaceholder) {
                    $this->NodeMAC = $NodeMAC;
                } else {
                    $this->NodeMAC = $NodeMAC2;
                }
                $this->Data = hex2bin($Command->Data);
            } else {
                if (!is_null($Command)) {
                    $this->Command = strtoupper($Command);
                }
                if (!is_null($NodeMAC)) {
                    $this->NodeMAC = strtoupper($NodeMAC);
                }
                if (!is_null($Data)) {
                    $this->Data = strtoupper($Data);
                }
            }
        }

        /**
         * Hex2Float
         *
         * @param  string $HexString
         * @return float
         */
        public static function Hex2Float(string $HexString): float
        {
            $bits = strrev(pack('H*', $HexString));
            return unpack('f', $bits)[1];
        }

        /**
         * IsValidMAC
         *
         * Prüft, ob der String eine MAC eines Nodes ist (16 Hex-Zeichen, Großbuchstaben).
         *
         * @param  string $NodeMAC
         * @return bool
         */
        public static function IsValidMAC(string $NodeMAC): bool
        {
            return preg_match('/^[0-9A-F]{16}$/', $NodeMAC) === 1;
        }

        /**
         * HexToInt
         *
         * Wandelt einen Hex-String in einen Integer um. hexdec() liefert auf Systemen mit 32 Bit Integer
         * für Werte über PHP_INT_MAX einen float. Ein solcher Wert wird auf $Max begrenzt.
         *
         * @param  string $Hex
         * @param  int $Max Obergrenze, Standard PHP_INT_MAX.
         * @return int
         */
        public static function HexToInt(string $Hex, int $Max = PHP_INT_MAX): int
        {
            $Value = hexdec($Hex);
            return ($Value > $Max) ? $Max : (int) $Value;
        }

        /**
         * DateTimeData2Timestamp
         *
         * Wandelt die Zeit des Circle+ (Sekunde, Minute, Stunde, Wochentag, Tag, Monat, Jahr je 2 Dezimalziffern,
         * siehe SetNetworkTime) in einen UTC-Timestamp um. Der Wochentag wird ignoriert.
         *
         * @param  string $Data 14 Dezimalziffern.
         * @return int|null null, wenn das Format nicht passt.
         */
        public static function DateTimeData2Timestamp(string $Data): ?int
        {
            if (preg_match('/^\d{14}$/', $Data) !== 1) {
                return null;
            }
            $Timestamp = gmmktime((int) substr($Data, 4, 2), (int) substr($Data, 2, 2), (int) substr($Data, 0, 2), (int) substr($Data, 10, 2), (int) substr($Data, 8, 2), 2000 + (int) substr($Data, 12, 2));
            return ($Timestamp === false) ? null : $Timestamp;
        }
        /**
         * NodeTimeNeedsSync
         *
         * Prüft, ob die Uhr eines Nodes neu gesetzt werden muss, z.B. nach einem Stromausfall (die Uhr steht dann auf 01.01.2000).
         *
         * @param  int $NodeTime Zeit des Nodes als UTC-Timestamp, <= 0 wenn ungültig (siehe Hex2Timestamp).
         * @param  int $Now Aktuelle Zeit als UTC-Timestamp.
         * @param  int $MaxDeviation Erlaubte Abweichung in Sekunden.
         * @return bool true, wenn die Zeit ungültig ist oder zu stark abweicht.
         */
        public static function NodeTimeNeedsSync(int $NodeTime, int $Now, int $MaxDeviation = 300): bool
        {
            return ($NodeTime <= 0) || (abs($NodeTime - $Now) > $MaxDeviation);
        }

        /**
         * Timestamp2Hex
         *
         * @param  int $TimeStamp
         * @return string
         */
        public static function Timestamp2Hex(int $TimeStamp): string
        {
            // Jahr (2 Zeichen), Monat (2 Zeichen) und Minuten seit Monatsanfang (immer 4 Zeichen, sonst verschiebt sich das Telegramm
            // in den ersten Tagen eines Monats, wenn der Wert unter 0x1000 liegt).
            $date = sprintf('%02X%02X%04X', gmdate('y', $TimeStamp), gmdate('m', $TimeStamp), ((gmdate('j', $TimeStamp) - 1) * 24 + gmdate('G', $TimeStamp)) * 60 + gmdate('i', $TimeStamp));
            $time = sprintf('%02X%02X%02X%02X', gmdate('G', $TimeStamp), gmdate('i', $TimeStamp), gmdate('s', $TimeStamp), gmdate('N', $TimeStamp) - 1);
            return $date . 'FFFFFFFF' . $time;
        }

        /**
         * Hex2Timestamp
         *
         * @param  string $TimeString
         * @return int
         */
        public static function Hex2Timestamp(string $TimeString): int
        {
            if ($TimeString == 'FFFFFFFF') {
                return -1;
            }
            if (preg_match('/^[0-9A-Fa-f]{8}/', $TimeString) !== 1) {
                return -1;
            }
            // Jahr (2 Zeichen), Monat (2 Zeichen), Minuten seit Monatsanfang (4 Zeichen)
            $Year = 2000 + self::HexToInt(substr($TimeString, 0, 2));
            $Month = self::HexToInt(substr($TimeString, 2, 2));
            $MinutesOfMonth = self::HexToInt(substr($TimeString, 4, 4));
            if (($Month < 1) || ($Month > 12) || ($MinutesOfMonth >= 31 * 24 * 60)) {
                return -1;
            }
            $Day = intdiv($MinutesOfMonth, 24 * 60) + 1;
            $MinutesOfDay = $MinutesOfMonth % (24 * 60);
            return gmmktime(intdiv($MinutesOfDay, 60), $MinutesOfDay % 60, 0, $Month, $Day, $Year);
        }

        /**
         * HexToSigned
         *
         * Wandelt einen Hex-String als vorzeichenbehafteten Wert (Zweierkomplement, Breite = 4 Bit je Zeichen) um.
         * Die Pulse und Zähler der Nodes haben ein Vorzeichen: Speist ein Gerät zurück (z.B. ein Wechselrichter),
         * sind sie negativ. Als vorzeichenloser Wert wären es riesige Zahlen (FFFE = 65534 statt -2).
         *
         * @param  string $Hex 4 Zeichen (16 Bit) oder 8 Zeichen (32 Bit).
         * @return int|float float nur auf Systemen mit 32 Bit Integer für große Beträge.
         */
        public static function HexToSigned(string $Hex): int|float
        {
            $Value = hexdec($Hex);
            $Range = pow(2, strlen($Hex) * 4);
            if ($Value >= $Range / 2) {
                $Value -= $Range;
            }
            return is_float($Value) && ($Value == floor($Value)) && (abs($Value) <= PHP_INT_MAX) ? (int) $Value : $Value;
        }

        /**
         * pulsesCorrection
         *
         * Berechnet aus den Pulsen eines Nodes die kalibrierten Pulse pro Sekunde (siehe pulsesToWatt und pulsesToKwh).
         * Das Vorzeichen bleibt erhalten: negative Pulse (Einspeisung) ergeben einen negativen Wert,
         * die Kalibrierung wird auf den Betrag angewendet.
         *
         * @param  string $PulsesInHex Pulse als Hex-String mit Vorzeichen (4 oder 8 Zeichen)
         * @param  int $TimeSpan
         * @param  float $offRuis
         * @param  float $offTot
         * @param  float $gainA
         * @param  float $gainB
         * @return float
         */
        public static function pulsesCorrection(string $PulsesInHex, int $TimeSpan, float $offRuis, float $offTot, float $gainA, float $gainB): float
        {
            $Signed = self::HexToSigned($PulsesInHex);
            if ($Signed == 0) {
                return 0.0;
            }
            $Sign = ($Signed < 0) ? -1.0 : 1.0;
            $value = abs($Signed) / $TimeSpan;
            return $Sign * (((pow($value + $offRuis, 2.0) * $gainB) + (($value + $offRuis) * $gainA)) + $offTot);
        }

        /**
         * pulsesToKwh
         *
         * $pulses ist der kalibrierte Wert in Pulsen pro Sekunde, geteilt durch 468.9385193 ergibt kW, über eine Stunde kWh.
         * Ob das 8-stellige Feld der PowerUsageResponse ein Gesamtzähler oder ein Stundenwert ist, ist ungeklärt (siehe STATUS.md).
         *
         * @param  float $pulses
         * @return float
         */
        public static function pulsesToKwh(float $pulses): float
        {
            return round((($pulses / 3600) / 468.9385193) * 3600, 3);
        }

        /**
         * pulsesToWatt
         *
         * Das Feld nanosecond_offset der Antwort wird nicht ausgewertet.
         *
         * @param  float $pulses
         * @return float
         */
        public static function pulsesToWatt(float $pulses): float
        {
            return round(($pulses / 468.9385193) * 1000, 3);
        }

        /**
         * DecodeFrame
         *
         * Zerlegt den String aus $Data in ein Frame-Objekt.
         * Wird beim Datenempfang vom Stick genutzt.
         *
         * @param string $Data Payload vom Stick
         * @return void
         */
        public function DecodeFrame(string $Data): void
        {
            $this->Command = strtoupper(substr($Data, 0, 4));
            $this->FrameID = hexdec(substr($Data, 4, 4));

            switch ($this->Command) {
                case Command::AdvertiseNodeResponse:
                    //   000D6F0000B1A240
                    //   Device MAC
                case Command::PingResponse:
                    //   000D6F0000B1B64B | xx  | xx   | xxxx
                    //   Circle+          | in? | out? | Time?
                case Command::StickStatusResponse:
                    // 000D6F0000B835CB | 01 | 01              | 44          |    0D6F0000B1B64B | 3B44 | FF
                    //    Stick-MAC     | ?? | Circle+ bekannt |  PANID High | 00 CirclePlusMAC  | PANID| ??
                    // 000D6F0000B835CB | 01 | 00 |
                    //    Stick-MAC     | ?? | kein Circle+ bekannt
                case Command::PowerUsageResponse:
                    //                  24|      28|      32|        40|         48|   52|
                    //                   0|       4|       8|        16|         24|   28|
                    //                      000F   | 0075   | 0000A3C1 | 00000000  | 0005
                    //   000D6F0000994CAA | 0000   | FFFF   | 00000000 | FFFFFDB9  | 000D
                    //   Circle MAC       | pulse1 | pulse8 |PulseTotal| PulseHour | ????
                    // pulse8 == 'FFFF' -> Spike, do not log
                case Command::AssociatedNodesResponse:
                    //   000D6F0000B1B64B | 000D6F0000B1B967 | 00
                    //   Circle+ MAC      | Device MAC       | Index
                case Command::RemoveNodeResponse:
                    //   000D6F00004BC34A | C4040E011610173A | 00
                    //   000D6F0000B1B64B | 000D6F0000B1B967 | 00
                    //   Circle+ MAC      | Device MAC       | 01 OK 00 Fehler
                case Command::InfoResponse:
                    //                 16|              24|        32|   34|  36|              48|              56| 58
                    //                  0|               8|        16|   18|  20|              32|              40| 42
                    //                     11   0A   5855   000515C0   01    85   0000 0440 0107   4E0844C2         02
                    //                     11   0A   5807   000515B8   00    85   0000 0440 0107   4E0844C2         02
                    //  000D6F0000994CAA   0F   08   595B   00044480   80    18   5653.9070.1402   4CCEC22A         02
                    //                     11   0A   3C98   0004ADE8   01    85   6539 0700 7326   4E0843A9         01
                    //                     00   00   0000   00000000   00    80   6539 0700 8512   4E0842BB         00 // Stick
                    //  000D6F0002588136   11   0A   52D2   00044038   01    85   6539 0700 7326   4E0843A9         01 // Circle
                    //                     00   01   0001   000515C8   01    85   0000 0440 0107   4E0844C2         02 // Switch?
                    // |  Circle+ MAC    |year|mon |min   | curr_log |state| HZ | HW1 .HW2 .HW3  | Firmware d/m/y |Typ
                    //             $year=2000+intval(hexdec(substr($msg, 16, 2)));
                    //            $month=intval(hexdec(substr($msg, 18, 2)));
                    //
                    //            $full_minutes=intval(hexdec(substr($msg, 20, 4)));
                    //            $str_date=$year."-".$month."-01 00:00";
                    //
                    //            try {
                    //                $date_obj=New DateTime($str_date);
                    //            }
                    //            catch (Exception $ex) {
                    //                $str_date="2010-01-01 00:00";
                    //                $date_obj=New DateTime($str_date);
                    //            }
                    //
                    //            $currentlog=(intval(hexdec(substr($msg, 24, 8)))- 278528) / 32;
                    //            $currentstate=substr($msg, 32, 2);
                    //            $hertz=substr($msg, 34, 2);
                    //            $hardware=sprintf("%s-%s-%s",substr($msg, 36, 4),substr($msg, 40, 4),substr($msg, 44, 4));
                    //            $firmware=date('d/m/Y', intval(hexdec(substr($msg, 48, 8))));
                    //            $Typ = substr($msg,60,2);
                case Command::CalibrationResponse:
                    //                   0|         8|        16|        24|       32|
                    //                      3F7F7F74   B5965F1F   BBBAB054   00000000
                    //   000D6F0000B1B64B | 3F7FA7CC | 3F7FA7CC | 3CD87C2F | 00000000
                    //   Circle+ MAC      | gainA    | gainB    | OffTot   | OffRuis
                    //            $macaddress=substr($msg,0,16);
                    //            $gainA = self::_hexToFloat(substr($msg, 16, 8));
                    //            $gainB = self::_hexToFloat(substr($msg, 24, 8));
                    //            $offTot = self::_hexToFloat(substr($msg, 32, 8));
                    //            $offRuis = self::_hexToFloat(substr($msg, 40, 8));
                    //            $this->_devices[$macaddress]["gainA"]=$gainA;
                    //            $this->_devices[$macaddress]["gainB"]=$gainB;
                    //            $this->_devices[$macaddress]["offTot"]=$offTot;
                    //            $this->_devices[$macaddress]["offRuis"]=$offRuis;
                case Command::DateTimeInfoResponse:
                    //   000D6F0000B1B64B | 20|43|12 | 06 | 12|05|11
                    //   Device? MAC      | s |i |h  |dow | d |m |y
                case Command::SendScheduleResponse:
                    //   000D6F0000B1A240 | xx
                    //   Device MAC       | ???
                case Command::ClockInfoResponse:
                    //   000D6F0000B1B64B | 0C | 1F | 07 | 06 | 01 | 457A
                    //   Circle+          | H  | M  | S  |DoW | ?? | scheduleCRC
                    //            $macaddress = substr($msg,0,16);
                    //            $hours = intval(hexdec(substr($msg, 16, 2)));
                    //            $minutes = intval(hexdec(substr($msg, 18, 2)));
                    //            $secondes = intval(hexdec(substr($msg, 20, 2)));
                    //            $day_of_week = intval(hexdec(substr($msg, 22, 2)));
                    //            $this->_devices[$macaddress]["clock_h"]=$hours;
                    //            $this->_devices[$macaddress]["clock_m"]=$minutes;
                    //            $this->_devices[$macaddress]["clock_s"]=$secondes;
                    //            $this->_devices[$macaddress]["clock_d"]=$day_of_week;
                case Command::PowerBufferResponse:
                    //                  16|              24|        32|                 48|                 64|                 96|
                    //   000D6F0000B1A240 | 0B | 04 | 7F80 | 00000A87 |0B|04|7FBC|00000ACF|0B|04|7FF8|00000AAC|FF|FF|FFFF|FFFFFFFF|00044460
                    //   Circle MAC       |year|mon |min   | curr_log |                   |  |  |    |        |  |  |    |        | address
                    //   $log_addr = ( hex($address) - 278528 ) / 8;
                    //   [later] I think the pairs of data is a power-value followed by a timestamp. The timestamp has the same structure as the date-word of the 0016 command. This means that  value 0B 04 7DA0 could mean Year 2011, Month April,  From the start of the month 32160 minutes, this is 536 hour, which is 22 days and 8 hour. Somewhere during the 23rd of april (yesterday), when the data was captured. So that is most likely.
                    //   [days later] The structure of this reply is explained in the plugwise unleased documents, and indeed 4 pairs of data, each pair represents one hour of accummulated power usage
                case Command::NodeAwakeResponse:
                    //   000D6F0000B1A240 | xx
                    //   Device MAC       | ??
                case Command::NodeSwitchGroupResponse:
                    //   000D6F0000B1A240 | xx | yy
                    //   Device MAC       | ?? | ??
                case Command::FeatureSetResponse:
                    //   000D6F0000B1A240 | 0123456789ABCDEF
                    //   Device MAC       | Feature
                case Command::AckAssociationResponse:
                    //   000D6F0000B1A240
                    //   Device MAC
                case Command::SensInfoResponse:
                    //   000D6F0000B1A240 | 01234 | 5678
                    //   Device MAC       | hum   | temp
                    //   (hex(hum)-3145)/524.30; Hum
                    //   (hex(temp)-17473)/372.90; Temp

                    $this->NodeMAC = strtoupper(substr($Data, 8, 16));
                    $this->Data = strtoupper(substr($Data, 24, -4));
                    break;
                    //-------------------------------------------only Data without MAC
                case Command::QueryCirclePlusResponse:
                    //     XX    | 000D6F0000B1B64B | 440D6F0000B1B64B | 440D6F0000B1B64B  | 440D6F0000B1B64B | 1234 | xx
                    //     0F    | FFFFFFFFFFFFFFFF | 6B0D6F0002588136 | FFFFFFFFFFFFFFFF  | 6B0D6F0002588136 | 2F6B | 01
                    //   channel |  Circle+ MAC     |   PANID-long     | unique_network_id | new_node_mac_id | PANID | ??
                case Command::ConnectCirclePlusResponse:
                    //       XX  | XX
                    // existing | allowed
                case Command::QueryCirclePlusResponseEnd:
                    //   xxxx
                    //   status
                case Command::AckMsgResponse:
                default:
                    $this->Data = strtoupper(substr($Data, 8, -4));
            }

            $Checksum = strtoupper(substr($Data, -4, 4));
            $this->Checksum = ($this->CalculateChecksum() == $Checksum ? true : false);
            //var_dump($this->Checksum);
        }

        /**
         * ToJSONStringForSplitter
         *
         * Erzeugt einen, mit der GUID versehenen, JSON-kodierten String zum Versand an den Splitter.
         *
         * @return string JSON-kodierter String für IPS-Dateninterface.
         */
        public function ToJSONStringForSplitter(): string
        {
            return json_encode(['DataID'  => '{E7DA1628-D62B-47BF-A834-E5556DD110E7}',
                'Command'                 => bin2hex($this->Command),
                'NodeMAC'                 => bin2hex($this->NodeMAC),
                'Data'                    => bin2hex($this->Data)
            ]);
        }

        /**
         * ToJSONStringForDevices
         *
         * Erzeugt einen, mit der GUID versehenen, JSON-kodierten String zum Versand an den Splitter.
         *
         * @return string
         */
        public function ToJSONStringForDevices(): string
        {
            return json_encode(['DataID'  => '{CD59EBB4-B313-4ACA-A503-E646CFE0B6FD}',
                'Command'                 => bin2hex($this->Command),
                'NodeMAC'                 => bin2hex($this->NodeMAC),
                'Data'                    => bin2hex($this->Data)
            ]);
        }

        /**
         * EncodeFrame
         *
         * @return string
         */
        public function EncodeFrame(): string
        {
            $Data = chr(0x05) . chr(0x05) . chr(0x03) . chr(0x03);
            $Data .= $this->Command . $this->NodeMAC . $this->Data . $this->CalculateChecksum();
            $Data .= chr(0x0D) . chr(0x0A);
            return $Data;
        }

        /**
         * CalculateChecksum
         *
         * this function is used to calculate the (common) crc16c for an entire buffer
         *
         * @return string
         */
        private function CalculateChecksum(): string
        {
            $buffer = $this->Command;
            if ($this->FrameID != -1) {
                $buffer .= sprintf('%04X', $this->FrameID);
            }
            $buffer .= $this->NodeMAC . $this->Data;
            $crc16c = 0x0000;
            $buffer_length = strlen($buffer);
            for ($i = 0; $i < $buffer_length; $i++) {
                $ch = ord($buffer[$i]);
                $crc16c = $this->update_common_crc16c($ch, $crc16c);
            }
            return sprintf('%04X', $crc16c); //strtoupper(str_pad(dechex($crc16c), 4, '0', STR_PAD_LEFT)); //mit nullen auffüllen
        }

        /**
         * update_common_crc16c
         *
         * this function is used to calculate the (common) crc16c byte by byte
         * $ch is the next byte and $crc16c is the result from the last call, or 0xffff initially
         *
         * @param  int $ch
         * @param  int $crc16c
         * @return int
         */
        private function update_common_crc16c(int $ch, int $crc16c): int
        {
            $crc16c_polynomial = 0x11021;
            $ch <<= 8;
            for ($i = 0; $i < 8; $i++) {
                if (($crc16c ^ $ch) & 0x8000) {
                    $xor_flag = true;
                } else {
                    $xor_flag = false;
                }
                $crc16c = $crc16c << 1;
                if ($xor_flag) {
                    $crc16c = $crc16c ^ $crc16c_polynomial;
                }
                $ch = $ch << 1;
            }
            // mask off (zero out) the upper two bytes
            $crc16c = $crc16c & 0x0000ffff;
            return $crc16c;
        }
    }

    /**
     * Sense
     *
     * Umrechnung der Rohwerte des Sense (SensInfoResponse).
     * T = 175.72 * Raw / 65536 - 46.85, H = 125 * Raw / 65536 - 6, Rohwert 65535 = kein Wert.
     */
    class Sense
    {
        public const NoValue = 65535;

        /**
         * Temperature
         *
         * @param  int $Raw
         * @return float|null null, wenn der Node keinen Wert geliefert hat.
         */
        public static function Temperature(int $Raw): ?float
        {
            return ($Raw === self::NoValue) ? null : (175.72 * $Raw / 65536 - 46.85);
        }

        /**
         * Humidity
         *
         * @param  int $Raw
         * @return float|null null, wenn der Node keinen Wert geliefert hat.
         */
        public static function Humidity(int $Raw): ?float
        {
            return ($Raw === self::NoValue) ? null : (125 * $Raw / 65536 - 6);
        }
    }

    /**
     * Presentation
     *
     * Darstellungsparameter der Statusvariablen. Die Parameter werden per Unit-Test gegen
     * die Parameterlisten von IPS_GetPresentation geprüft (tests/PresentationParameters.json).
     */
    class Presentation
    {
        /**
         * Reachable
         *
         * @param  string $Alert Beschriftung für false.
         * @param  string $Ok Beschriftung für true.
         * @return array
         */
        public static function Reachable(string $Alert, string $Ok): array
        {
            return [
                'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                'ICON'         => 'Warning',
                'PREFIX'       => '',
                'SUFFIX'       => '',
                'USAGE_TYPE'   => 0,
                'OPTIONS'      => json_encode([
                    self::BooleanOption(false, $Alert, 16711680),
                    self::BooleanOption(true, $Ok, -1),
                ])
            ];
        }

        /**
         * Frequency
         *
         * @return array
         */
        public static function Frequency(): array
        {
            return [
                'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                'ICON'             => 'Electricity',
                'DIGITS'           => 2,
                'INTERVALS'        => '[]',
                'INTERVALS_ACTIVE' => false,
                'PERCENTAGE'       => false,
                'PREFIX'           => '',
                'SUFFIX'           => ' Hz',
                'USAGE_TYPE'       => 0,
            ];
        }

        /**
         * Motion
         *
         * @param  string $NoMotion Beschriftung für false.
         * @param  string $Motion Beschriftung für true.
         * @return array
         */
        public static function Motion(string $NoMotion, string $Motion): array
        {
            return [
                'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                'ICON'         => 'person-walking',
                'PREFIX'       => '',
                'SUFFIX'       => '',
                'USAGE_TYPE'   => 0,
                'OPTIONS'      => json_encode([
                    self::BooleanOption(false, $NoMotion, -1),
                    self::BooleanOption(true, $Motion, 16750848),
                ])
            ];
        }

        /**
         * Power
         *
         * Leistung in Watt (aktuell und Durchschnitt).
         *
         * @return array
         */
        public static function Power(): array
        {
            return self::Numeric(' W', ' mW', ' kW');
        }

        /**
         * EnergyOverall
         *
         * Energie in kWh.
         *
         * @return array
         */
        public static function EnergyOverall(): array
        {
            return self::Numeric(' kWh', ' Wh', ' MWh');
        }

        /**
         * Temperature
         *
         * @return array
         */
        public static function Temperature(): array
        {
            return [
                'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                'MIN'              => -30,
                'MAX'              => 70,
                'DIGITS'           => 1,
                'ICON'             => 'Temperature',
                'INTERVALS'        => '[]',
                'INTERVALS_ACTIVE' => false,
                'PERCENTAGE'       => false,
                'PREFIX'           => '',
                'SUFFIX'           => ' °C',
                'USAGE_TYPE'       => 1,
            ];
        }

        /**
         * Humidity
         *
         * @return array
         */
        public static function Humidity(): array
        {
            return [
                'PRESENTATION'     => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                'MIN'              => 0,
                'MAX'              => 100,
                'DIGITS'           => 1,
                'ICON'             => 'Gauge',
                'INTERVALS'        => '[]',
                'INTERVALS_ACTIVE' => false,
                'PERCENTAGE'       => true,
                'PREFIX'           => '',
                'SUFFIX'           => ' %',
                'USAGE_TYPE'       => 0,
            ];
        }

        /**
         * Numeric
         *
         * @param  string $Suffix Einheit der Variable.
         * @param  string $SmallSuffix Einheit für Werte im Bereich 0,001 bis 1.
         * @param  string $LargeSuffix Einheit für Werte im Bereich 1000 bis 999999.
         * @return array
         */
        private static function Numeric(string $Suffix, string $SmallSuffix, string $LargeSuffix): array
        {
            return [
                'PRESENTATION'        => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
                'ICON'                => 'bolt',
                'DECIMAL_SEPARATOR'   => 'Client',
                'COLOR'               => -1,
                'DISPLAY_TYPE'        => 0,
                'CONTENT_COLOR'       => -1,
                'DIGITS'              => 2,
                'INTERVALS'           => json_encode([
                    self::Interval(0.001, 1, 0.001, $SmallSuffix, false),
                    self::Interval(1000, 999999, 1000, $LargeSuffix, true),
                ]),
                'INTERVALS_ACTIVE'    => true,
                'MIN'                 => 0,
                'MAX'                 => 100,
                'PERCENTAGE'          => false,
                'PREFIX'              => '',
                'PREVIEW_STYLE'       => 1,
                'SHOW_PREVIEW'        => true,
                'SUFFIX'              => $Suffix,
                'THOUSANDS_SEPARATOR' => '',
                'USAGE_TYPE'          => 0,
            ];
        }

        /**
         * Interval
         *
         * @param  float $Min
         * @param  float $Max
         * @param  float $Factor
         * @param  string $Suffix
         * @param  bool $Digits
         * @return array
         */
        private static function Interval(float $Min, float $Max, float $Factor, string $Suffix, bool $Digits): array
        {
            return [
                'IntervalMinValue'   => $Min,
                'IntervalMaxValue'   => $Max,
                'ConstantActive'     => false,
                'ConstantValue'      => '',
                'ConversionFactor'   => $Factor,
                'PrefixActive'       => false,
                'PrefixValue'        => '',
                'SuffixActive'       => true,
                'SuffixValue'        => $Suffix,
                'DigitsActive'       => $Digits,
                'DigitsValue'        => 2,
                'IconActive'         => false,
                'IconValue'          => '',
                'ColorActive'        => false,
                'ColorValue'         => -1,
                'ContentColorActive' => false,
                'ContentColorValue'  => -1,
            ];
        }

        /**
         * BooleanOption
         *
         * @param  bool $Value
         * @param  string $Caption
         * @param  int $Color
         * @return array
         */
        private static function BooleanOption(bool $Value, string $Caption, int $Color): array
        {
            return [
                'Value'              => $Value,
                'Caption'            => $Caption,
                'IconActive'         => false,
                'IconValue'          => '',
                'ColorActive'        => true,
                'ColorValue'         => $Color,
                'ContentColorActive' => false,
                'ContentColorValue'  => -1,
            ];
        }
    }
    /**
     * FormHelper
     *
     * Setzt Eigenschaften von Elementen eines Konfigurationsformulars über den Namen (`name`) statt über den Index.
     */
    trait FormHelper
    {
        /**
         * Setzt eine Eigenschaft des ersten Elements mit dem Namen $Name (rekursiv in allen Ebenen des Formulars).
         *
         * @param  array $Form Das Formular oder ein Teil davon.
         * @param  string $Name Wert von `name` des Elements.
         * @param  string $Property Zu setzende Eigenschaft, z.B. `visible`, `enabled` oder `values`.
         * @param  mixed $Value Neuer Wert.
         * @return bool true, wenn das Element gefunden wurde.
         */
        protected function SetFormProperty(array &$Form, string $Name, string $Property, mixed $Value): bool
        {
            foreach ($Form as &$Element) {
                if (!is_array($Element)) {
                    continue;
                }
                if (($Element['name'] ?? null) === $Name) {
                    $Element[$Property] = $Value;
                    return true;
                }
                if ($this->SetFormProperty($Element, $Name, $Property, $Value)) {
                    return true;
                }
            }
            return false;
        }
    }
    /**
     * DebugHelper ergänzt SendDebug um die Möglichkeit Array und Objekte auszugeben.
     * Eigene Variante neben libs/helper/DebugHelper.php: Plugwise\Frame-Objekte werden lesbar ausgegeben.
     *
     */
    trait DebugHelper
    {
        /**
         * Ergänzt SendDebug um Möglichkeit Objekte und Array auszugeben.
         *
         *
         * @param string $Message Nachricht für Data.
         * @param mixed $Data Daten für die Ausgabe.
         * @param int $Format Ausgabeformat für Strings.
         * @return bool
         */
        protected function SendDebug(string $Message, mixed $Data, int $Format): bool
        {
            if (is_a($Data, 'Plugwise\Frame')) {
                /* @var $Data Frame */
                $this->SendDebug($Message . ':Command', Command::ToString($Data->Command), 0);
                if ($Data->FrameID !== -1) {
                    $this->SendDebug($Message . ':FrameID', $Data->FrameID, 0);
                }
                if ($Data->NodeMAC !== '') {
                    $this->SendDebug($Message . ':NodeMAC', $Data->NodeMAC, 0);
                }
                if ($Data->Data !== '') {
                    $this->SendDebug($Message . ':Payload', $Data->Data, 0);
                }
                if ($Data->Checksum !== null) {
                    $this->SendDebug($Message . ':Checksum', $Data->Checksum, 0);
                }
            } elseif (is_array($Data)) {
                foreach ($Data as $Key => $DebugData) {
                    $this->SendDebug($Message . ':' . $Key, $DebugData, 0);
                }
            } elseif (is_object($Data)) {
                foreach ($Data as $Key => $DebugData) {
                    $this->SendDebug($Message . '.' . $Key, $DebugData, 0);
                }
            } elseif (is_bool($Data)) {
                parent::SendDebug($Message, ($Data ? 'TRUE' : 'FALSE'), 0);
            } else {
                if (IPS_GetKernelRunlevel() == KR_READY) {
                    parent::SendDebug($Message, (string) $Data, $Format);
                } else {
                    $this->LogMessage($Message . ':' . (string) $Data, KL_DEBUG);
                }
            }
            return true;
        }
    }
}