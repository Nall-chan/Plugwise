<?php

declare(strict_types=1);

/**
 * @addtogroup plugwise
 * @{
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2019 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.1
 * @example <b>Ohne</b>
 */

namespace Plugwise;

eval('declare(strict_types=1);namespace Plugwise {?>' . file_get_contents(__DIR__ . '/helper/BufferHelper.php') . '}');
eval('declare(strict_types=1);namespace Plugwise {?>' . file_get_contents(__DIR__ . '/helper/SemaphoreHelper.php') . '}');
eval('declare(strict_types=1);namespace Plugwise {?>' . file_get_contents(__DIR__ . '/helper/ParentIOHelper.php') . '}');
eval('declare(strict_types=1);namespace Plugwise {?>' . file_get_contents(__DIR__ . '/helper/VariableProfileHelper.php') . '}');

class Plugwise_NetworkState
{
    const StickNotFound = 0;
    const CirclePlusMissing = 1;
    const CirclePlusOffline = 2;
    const SearchingCirclePlus = 3;
    const ParingCirclePlus = 4;
    const SearchingNodes = 5;
    const Online = 7;
    const OnlineJoining = 8;

    public static function ToString($NetworkState)
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
                return $NetworkState;
        }
    }
}

class Plugwise_Switch
{
    const ON = '01';
    const OFF = '00';

    public static $Hertz = [
        133 => 50,
        197 => 60
    ];

    public static function ToString($Plugwise_Switch)
    {
        switch ($Plugwise_Switch) {
            case self::ON:
                return 'ON';
            case self::OFF:
                return 'OFF';
            default:
                return '????????????';
        }
    }
}

class Plugwise_AckMsg
{
    const ACK = '00C1';
    const NACK = '00C2';
    const UNKNOW = '00C3';          //	"Command not allowed"
    const SWITCHON = '00D8';
    const JOININGENABLE = '00D9';   //	"Allow nodes to join ACK1"
    const JOININGDISABLE = '00DD';  //	"Allow nodes to join ACK0"
    const SWITCHOFF = '00DE';
    const SUCCESSFUL = '00DF';      //	"Set RTC-Data ACK"
    //                 "00E7";      //	"Set RTC-Data NACK"
    const OUTOFRANGE = '00E1';
    const DISCONNECTED = '00F2';    //	"Reply role changed OK"
    //                  "00F3";     //	"Reply role changed NOK"
    const CONNECTED = '00F4';       //	"Set handle on"
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
    const SETTIMEACK = '00D7';     //	"Set year, month and flashadress DONE"

    //  	 	"00BD";     //	"Start Light-Calibration started"
    //  	 	"00E9";     //	"Start Pingrun ACK"
    //  	 	"00EA";     //	"Stop Pingrun ACK"
    //  	 	"00DC";     //	"Synchronize NC ACK"
    //  	 	"00D6";     //	"Timeout Powermeter Logdata"

    public static function ToString($Plugwise_AckMsg)
    {
        switch ($Plugwise_AckMsg) {
            case self::ACK:
                return 'ACK';
            case self::NACK:
                return 'NACK';
            case self::UNKNOW:
                return 'UNKNOW';
            case self::SWITCHON:
                return 'SWITCHON';
            case self::JOININGENABLE:
                return 'JOININGENABLE';
            case self::JOININGDISABLE:
                return 'JOININGDISABLE';
            case self::SWITCHOFF:
                return 'SWITCHOFF';
            case self::SUCCESSFUL:
                return 'SUCCESSFUL';
            case self::OUTOFRANGE:
                return 'OUTOFRANGE';
            case self::DISCONNECTED:
                return 'DISCONNECTED';
            case self::CONNECTED:
                return 'CONNECTED';
            default:
                return $Plugwise_AckMsg . ' = ????????????';
        }
    }
}

class Plugwise_Command
{
    /**
     * Response from stick after request
     */
    const AckMsgResponse = '0000';

    /**
     * Query any presence off networks. Maybe intended to find a Circle+ from the Stick
     */
    const QueryCirclePlusRequest = '0001';
    const QueryCirclePlusResponse = '0002';
    const QueryCirclePlusResponseEnd = '0003';

    /**
     * Request connection to the network. Maybe intended to connect a Circle+ to the Stick
     */
    const ConnectCirclePlusRequest = '0004';
    const ConnectCirclePlusResponse = '0005';

    /**
     * Broadcast from factory-default nodes
     */
    const AdvertiseNodeResponse = '0006';

    /**
     * Send Join nodes request to add a new node to the network
     */
    const JoinNodeRequest = '0007';

    /*
     * Send a flag which enables or disables joining nodes request
     */
    const EnableJoiningRequest = '0008';

    /**
     * Send preset circle request
     */
    const ResetRequest = '0009';

    /**
     * message for that initializes the Stick
     */
    const StickStatusRequest = '000A';

    /**
     * Send ping to node
     */
    const Ping = '000D';
    const PingResponse = '000E';

    /**
     * Status from stick
     */
    const StickStatusResponse = '0011';

    /**
     * Request for power usage
     */
    const PowerUsageRequest = '0012';

    /**
     * returns power usage as impulse counters for several different TimeFrames
     */
    const PowerUsageResponse = '0013';

    /**
     * Set time on circle+
     */
    const ClockSetRequest = '0016'; // an Circle

    /**
     * switches Plug on or off
     */
    const SwitchRequest = '0017';

    /**
     * Send populate request
     */
    const AssociatedNodesRequest = '0018';

    /**
     * AssociatedNodes response
     */
    const AssociatedNodesResponse = '0019';

    /**
     * Request remove node from network
     */
    const RemoveNodeRequest = '001C';

    /**
     * RemoveNode response
     */
    const RemoveNodeResponse = '001D';

    /**
     * Info request and response
     */
    const InfoRequest = '0023';
    const InfoResponse = '0024';

    /**
     * Calibration request and response
     */
    const CalibrationRequest = '0026';
    const CalibrationResponse = '0027';

    /**
     * DateTime request and response
     */
    const SetDateTimeRequest = '0028';
    const DateTimeInfoRequest = '0029';
    const DateTimeInfoResponse = '003A';

    /**
     * Send chunk of On/Off/StandbyKiller Schedule to Stick
     */
    const PrepareScheduleRequest = '003B';

    /**
     * Send chunk of  On/Off/StandbyKiller Schedule to Circle(+)
     */
    const SendScheduleRequest = '003C';
    const SendScheduleResponse = '003D';

    /**
     * Clock request and response
     */
    const ClockInfoRequest = '003E';
    const ClockInfoResponse = '003F';

    /**
     * switches Schedule on or off
     */
    const EnableScheduleRequest = '0040';

    /**
     * Request power usage historical data
     */
    const PowerBufferRequest = '0048';

    /**
     * returns information about historical power usage
     * each response contains 4 log buffers and each log buffer contains data for 1 hour
     */
    const PowerBufferResponse = '0049';

    /**
     * Eventuell Anlernmodus Circle+ ???
     */
    const GetCirclePlus1 = '004A';

    /**
     * Verbundenen Circle+ anfordern.
     */
    const GetConnectedCirclePlus = '004E';

    /**
     * PushButtons
     */
    const PushButtonResponse = '004F';

    /**
     * Keypress on Switch
     */
    const KeyPressResponse = '0056';

    /**
     * ???
     */
    const LogIntervalRequest = '0057';

    /**
     * ???
     */
    const ClearGroupMacRequest = '0058';

    /**
     * Send chunk of  On/Off/StandbyKiller Schedule to Circle(+)
     */
    const SetScheduleValueRequest = '0059';

    /**
     * ???
     */
    const FeatureSetRequest = '005F';

    /**
     * returns feature set of modules
     */
    const FeatureSetResponse = '0060';

    /**
     * Broadcast when node join network
     */
    const AckAssociationResponse = '0061';

    /**
     * Sens values
     */
    const SensInfoResponse = '0105';

    public static function ToString($Plugwise_Command)
    {
        switch ($Plugwise_Command) {
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
                return 'StatusResponse';
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
            case self::GetConnectedCirclePlus:
                return 'GetCirclePlus';
            case self::PushButtonResponse:
                return 'PushButtonResponse';
            case self::KeyPressResponse:
                return 'KeyPressResponse';
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
                return $Plugwise_Command . ' ????';
        }
    }
}

class Plugwise_Typ
{
    const unknow = 0;
    const Cricle = 1;
    const Switche = 2; //No typo, switch is reserved by PHP ;)
    const Sense = 3;
    const Scan = 4;

    public static $Type = [
        0    => self::Cricle,
        '00' => self::Cricle,
        '01' => self::Cricle,
        '02' => self::Cricle,
        '03' => self::Switche,
        '04' => self::Switche,
        '05' => self::Sense,
        '06' => self::Scan,
    ];

    public static function ToString($Plugwise_Type)
    {
        switch ($Plugwise_Type) {
            case self::Cricle:
                return 'Circle';
            case self::Switche:
                return 'Switch';
            case self::Sense:
                return 'Sense';
            case self::Scan:
                return 'Scan';
            default:
                return $Plugwise_Type;
        }
    }
}

/**
 * Enthält einen Plugwise Datenpaket.
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.1
 * @example <b>Ohne</b>
 *
 *
 * @property string $Command Kommando
 * @property int $FrameID Frame-ID
 * @property string $NodeMAC ID des Circle
 * @property string $Data Payload
 * @property bool $Checksum Checksumme
 */
class Plugwise_Frame
{
    const CirclePlusMac = 'FFFFFFFF';

    /**
     * Kommando
     * @access private
     * @var string
     */
    public $Command = '';

    /**
     * Frame-ID
     * @access private
     * @var int
     */
    public $FrameID = -1;

    /**
     * ID des Circle
     * @access private
     * @var string
     */
    public $NodeMAC = '';

    /**
     * Payload
     * @access private
     * @var string
     */
    public $Data = '';

    /**
     * Checksumme
     * @access private
     * @var bool
     */
    public $Checksum = null;

    /**
     * Erstellt ein Plugwise_Frame Objekt.
     *
     * @access public
     * @param string $Command [optional] Kommando
     * @param string $NodeMAC [optional] ID des Circle
     * @param string $Data [optional] Payload
     * @return Plugwise_Frame
     */
    public function __construct($Command = null, $NodeMAC = null, $Data = null)
    {
        if (is_object($Command)) {
            $this->Command = utf8_decode($Command->Command);

            $NodeMAC2 = utf8_decode($Command->NodeMAC);
            if ($NodeMAC2 == self::CirclePlusMac) {
                $this->NodeMAC = $NodeMAC;
            } else {
                $this->NodeMAC = $NodeMAC2;
            }
            $this->Data = utf8_decode($Command->Data);
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

    public static function Hex2Float($HexString)
    {
        $intval = hexdec($HexString);
        $bits = pack('L', $intval);
        return unpack('f', $bits)[1];
    }

    public static function Timestamp2Hex($TimeStamp)
    {
        $date = sprintf('%02X%02X%02X', gmdate('y', $TimeStamp), gmdate('m', $TimeStamp), ((gmdate('j', $TimeStamp) - 1) * 24 + gmdate('G', $TimeStamp)) * 60 + gmdate('i', $TimeStamp));
        $time = sprintf('%02X%02X%02X%02X', gmdate('G', $TimeStamp), gmdate('i', $TimeStamp), gmdate('s', $TimeStamp), gmdate('N', $TimeStamp));
        return $date . 'FFFFFFFF' . $time;
    }

    public static function Hex2Timestamp($TimeString)
    {
        if ($TimeString == 'FFFFFFFF') {
            return;
        }
        $circle_date = sprintf('%04d-%02d-%02d', hexdec(substr($TimeString, 0, 2)) + 2000, hexdec(substr($TimeString, 2, 2)), (hexdec(substr($TimeString, 4, 4)) / 60 / 24) + 1);
        $time = hexdec(substr($TimeString, 4, 4)) % (60 * 24);
        $hour = intval($time / 60);
        $minutes = $time % 60;
        $circle_time = sprintf('%02d:%02d', $hour, $minutes);
        return strtotime($circle_date . $circle_time . ' UTC');
    }

    public static function pulsesCorrection($pulses, $timespan, $offRuis, $offTot, $gainA, $gainB)
    {
        if ($pulses == 0) {
            $out = 0;
        } else {
            $value = $pulses / $timespan;
            $out = 1 * (((pow($value + $offRuis, 2.0) * $gainB) + (($value + $offRuis) * $gainA)) + $offTot);
        }
        return $out;
    }

    public static function pulsesToKwh($pulses)
    {
        return (($pulses / 3600) / 468.9385193) * 3600;
    }

    public static function pulsesToWatt($pulses)
    {
        return ($pulses / 468.9385193) * 1000;
        //return (number_format($result, 3, ',', ''));
    }

    /**
     * Zerlegt den String aus $Data in ein Plugwise_Frame-Objekt.
     * Wird beim Datenempfang vom Stick genutzt.
     *
     * @access public
     * @param string $Data Payload vom Stick
     */
    public function DecodeFrame($Data)
    {
        $this->Command = strtoupper(substr($Data, 0, 4));
        $this->FrameID = hexdec(substr($Data, 4, 4));

        switch ($this->Command) {

            case Plugwise_Command::AdvertiseNodeResponse:
            //   000D6F0000B1A240
            //   Device MAC
            case Plugwise_Command::PingResponse:
            //   000D6F0000B1B64B | xx  | xx   | xxxx
            //   Circle+          | in? | out? | Time?
            case Plugwise_Command::StickStatusResponse:
            // 000D6F0000B835CB | 01 | 01              | 440D6F0000B1B64B | 3B44 | FF
            //    Stick-MAC     | ?? | Circle+ bekannt |   PANID-long     | PANID| ??
            // 000D6F0000B835CB | 01 | 00 |
            //    Stick-MAC     | ?? | kein Circle+ bekannt
            case Plugwise_Command::PowerUsageResponse:
            //                  24|      28|      32|        40|         48|   52|
            //                   0|       4|       8|        16|         24|   28|
            //                      000F   | 0075   | 0000A3C1 | 00000000  | 0005
            //   000D6F0000994CAA | 0000   | FFFF   | 00000000 | FFFFFDB9  | 000D
            //   Circle MAC       | pulse1 | pulse8 |PulseTotal| PulseHour | ????
            // pulse8 == 'FFFF' -> Spike, do not log
            case Plugwise_Command::AssociatedNodesResponse:
            //   000D6F0000B1B64B | 000D6F0000B1B967 | 00
            //   Circle+ MAC      | Device MAC       | Index
            case Plugwise_Command::RemoveNodeResponse:
            //   000D6F00004BC34A | C4040E011610173A | 00
            //   000D6F0000B1B64B | 000D6F0000B1B967 | 00
            //   Circle+ MAC      | Device MAC       | Index
            case Plugwise_Command::InfoResponse:
            //                 16|              24|        32|   34|  36|              48|              56| 58
            //                  0|               8|        16|   18|  20|              32|              40| 42
            //                     11   0A   5855   000515C0   01    85   0000 0440 0107   4E0844C2         02
            //                     11   0A   5807   000515B8   00    85   0000 0440 0107   4E0844C2         02
            // |000D6F0000994CAA | 0F | 08 | 595B | 00044480 | 80  | 18 | 5653.9070.1402 | 4CCEC22A       | 02
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
            case Plugwise_Command::CalibrationResponse:
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
            case Plugwise_Command::DateTimeInfoResponse:
            //   000D6F0000B1B64B | 20|43|12 | 06 | 12|05|11
            //   Device? MAC      | s |i |h  |dow | d |m |y
            case Plugwise_Command::SendScheduleResponse:
            //   000D6F0000B1A240 | xx
            //   Device MAC       | ???
            case Plugwise_Command::ClockInfoResponse:
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
            case Plugwise_Command::PowerBufferResponse:
            //                  16|              24|        32|                 48|                 64|                 96|
            //   000D6F0000B1A240 | 0B | 04 | 7F80 | 00000A87 |0B|04|7FBC|00000ACF|0B|04|7FF8|00000AAC|FF|FF|FFFF|FFFFFFFF|00044460
            //   Circle MAC       |year|mon |min   | curr_log |                   |  |  |    |        |  |  |    |        | address
            //   $log_addr = ( hex($address) - 278528 ) / 8;
            //   [later] I think the pairs of data is a power-value followed by a timestamp. The timestamp has the same structure as the date-word of the 0016 command. This means that  value 0B 04 7DA0 could mean Year 2011, Month April,  From the start of the month 32160 minutes, this is 536 hour, which is 22 days and 8 hour. Somewhere during the 23rd of april (yesterday), when the data was captured. So that is most likely.
            //   [days later] The structure of this reply is explained in the plugwise unleased documents, and indeed 4 pairs of data, each pair represents one hour of accummulated power usage
            case Plugwise_Command::PushButtonResponse:
            //   000D6F0000B1A240 | xx
            //   Device MAC       | ??
            case Plugwise_Command::KeyPressResponse:
            //   000D6F0000B1A240 | xx | yy
            //   Device MAC       | ?? | ??
            case Plugwise_Command::FeatureSetResponse:
            //   000D6F0000B1A240 | 0123456789ABCDEF
            //   Device MAC       | Feature
            case Plugwise_Command::AckAssociationResponse:
            //   000D6F0000B1A240
            //   Device MAC
            case Plugwise_Command::SensInfoResponse:
                //   000D6F0000B1A240 | 01234 | 5678
                //   Device MAC       | hum   | temp
                //   (hex(hum)-3145)/524.30; Hum
                //   (hex(temp)-17473)/372.90; Temp

                $this->NodeMAC = strtoupper(substr($Data, 8, 16));
                $this->Data = strtoupper(substr($Data, 24, -4));
                break;
//-------------------------------------------only Data without MAC
            case Plugwise_Command::QueryCirclePlusResponse:
            //     XX    | 000D6F0000B1B64B | 440D6F0000B1B64B | 440D6F0000B1B64B  | 440D6F0000B1B64B | 1234 | xx
            //     0F    | FFFFFFFFFFFFFFFF | 6B0D6F0002588136 | FFFFFFFFFFFFFFFF  | 6B0D6F0002588136 | 2F6B | 01
            //   channel |  Circle+ MAC     |   PANID-long     | unique_network_id | new_node_mac_id | PANID | ??
            case Plugwise_Command::ConnectCirclePlusResponse:
            //       XX  | XX
            // existing | allowed
            case Plugwise_Command::QueryCirclePlusResponseEnd:
            //   xxxx
            //   status
            case Plugwise_Command::AckMsgResponse:
            default:
                $this->Data = strtoupper(substr($Data, 8, -4));
        }

        $Checksum = strtoupper(substr($Data, -4, 4));
        $this->Checksum = ($this->CalculateChecksum() == $Checksum ? true : false);
        //var_dump($this->Checksum);
    }

    /**
     * Erzeugt einen, mit der GUDI versehenen, JSON-kodierten String zum versand an den Splitter.
     *
     * @access public
     * @param string $GUID Die Interface-GUID welche mit in den JSON-String integriert werden soll.
     * @return string JSON-kodierter String für IPS-Dateninterface.
     */
    public function ToJSONStringForSplitter()
    {
        return json_encode(['DataID'  => '{E7DA1628-D62B-47BF-A834-E5556DD110E7}',
            'Command'                 => utf8_encode($this->Command),
            'NodeMAC'                 => utf8_encode($this->NodeMAC),
            'Data'                    => utf8_encode($this->Data)
        ]);
    }

    public function ToJSONStringForDevices()
    {
        return json_encode(['DataID'  => '{CD59EBB4-B313-4ACA-A503-E646CFE0B6FD}',
            'Command'                 => utf8_encode($this->Command),
            'NodeMAC'                 => utf8_encode($this->NodeMAC),
            'Data'                    => utf8_encode($this->Data)
        ]);
    }

    public function EncodeFrame()
    {
        $Data = chr(0x05) . chr(0x05) . chr(0x03) . chr(0x03);
        $Data .= $this->Command . $this->NodeMAC . $this->Data . $this->CalculateChecksum();
        $Data .= chr(0x0D) . chr(0x0A);
        return $Data;
    }

    // this function is used to calculate the (common) crc16c for an entire buffer
    private function CalculateChecksum()
    {
        $buffer = $this->Command;
        if ($this->FrameID != -1) {
            $buffer .= sprintf('%04X', $this->FrameID);
        }
        $buffer .= $this->NodeMAC . $this->Data;
        $crc16c = 0x0000;  // the crc initial value laut www.maartendamen.com
        $buffer_length = strlen($buffer);
        for ($i = 0; $i < $buffer_length; $i++) {
            $ch = ord($buffer[$i]);
            $crc16c = $this->update_common_crc16c($ch, $crc16c);
        }
        return sprintf('%04X', $crc16c); //strtoupper(str_pad(dechex($crc16c), 4, '0', STR_PAD_LEFT)); //mit nullen auffüllen
    }
    // this function is used to calculate the (common) crc16c byte by byte
    // $ch is the next byte and $crc16c is the result from the last call, or 0xffff initially
    private function update_common_crc16c($ch, $crc16c)
    {
        $crc16c_polynomial = 0x11021;   //auch laut maartendamen
        // This comment was in the code from
        // http://www.joegeluso.com/software/articles/ccitt.htm
        // Why are they shifting this byte left by 8 bits??
        // How do the low bits of the poly ever see it?
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
 * DebugHelper ergänzt SendDebug um die Möglichkeit Array und Objekte auszugeben.
 *
 */
trait DebugHelper
{
    /**
     * Ergänzt SendDebug um Möglichkeit Objekte und Array auszugeben.
     *
     * @access protected
     * @param string $Message Nachricht für Data.
     * @param array|object|bool|string|int $Data Daten für die Ausgabe.
     * @return int $Format Ausgabeformat für Strings.
     */
    protected function SendDebug($Message, $Data, $Format)
    {
        if (is_a($Data, 'Plugwise\Plugwise_Frame')) {
            /* @var $Data Plugwise_Frame */
            $this->SendDebug($Message . ':Command', Plugwise_Command::ToString($Data->Command), 0);
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
        } elseif (is_a($Data, 'Plugwise\Plugwise_Data')) {
            /* @var $Data Plugwise_Data */
            $this->SendDebug($Message . ':Command', Plugwise_Command::ToString($Data->Command), 0);
            if ($Data->NodeMAC !== '') {
                $this->SendDebug($Message . ':NodeMAC', $Data->NodeMAC, 0);
            }
            if ($Data->Data !== '') {
                $this->SendDebug($Message . ':Payload', $Data->Data, 0);
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
    }
}

/**
 * Ein Trait welcher es ermöglicht über einen Ident Variablen zu beschreiben.
 */
trait VariableHelper
{
    /**
     * Setzte eine IPS-Variable vom Typ bool auf den Wert von $value
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param bool $Value Neuer Wert der Statusvariable.
     * @return bool true wenn Variable vorhanden sonst false.
     */
    protected function SetValueBoolean($Ident, $Value, $Profile = '')
    {
        $this->RegisterVariableBoolean(str_replace(' ', '', $Ident), $this->Translate($Ident), $Profile);
        $this->SetValue($Ident, (bool) $Value);
        return true;
    }

    /**
     * Setzte eine IPS-Variable vom Typ integer auf den Wert von $value.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param int $Value Neuer Wert der Statusvariable.
     * @return bool true wenn Variable vorhanden sonst false.
     */
    protected function SetValueInteger($Ident, $Value, $Profile = '')
    {
        $this->RegisterVariableInteger(str_replace(' ', '', $Ident), $this->Translate($Ident), $Profile);
        $this->SetValue($Ident, (int) $Value);
        return true;
    }

    /**
     * Setzte eine IPS-Variable vom Typ float auf den Wert von $value.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param float $Value Neuer Wert der Statusvariable.
     * @return bool true wenn Variable vorhanden sonst false.
     */
    protected function SetValueFloat($Ident, $Value, $Profile = '')
    {
        $this->RegisterVariableFloat(str_replace(' ', '', $Ident), $this->Translate($Ident), $Profile);
        $this->SetValue($Ident, (float) $Value);
        return true;
    }

    /**
     * Setzte eine IPS-Variable vom Typ string auf den Wert von $value.
     *
     * @access protected
     * @param string $Ident Ident der Statusvariable.
     * @param string $Value Neuer Wert der Statusvariable.
     * @return bool true wenn Variable vorhanden sonst false.
     */
    protected function SetValueString($Ident, $Value, $Profile = '')
    {
        $this->RegisterVariableString(str_replace(' ', '', $Ident), $this->Translate($Ident), $Profile);
        $this->SetValue($Ident, (string) $Value);
        return true;
    }
}

/** @} */
