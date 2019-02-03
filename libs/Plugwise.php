<?php

/**
 * @addtogroup plugwise
 * @{
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2018 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.0
 * @example <b>Ohne</b>
 */

if (!defined("IPS_BASE")) {
    // --- BASE MESSAGE
    define('IPS_BASE', 10000);                             //Base Message
    define('IPS_KERNELSTARTED', IPS_BASE + 1);             //Post Ready Message
    define('IPS_KERNELSHUTDOWN', IPS_BASE + 2);            //Pre Shutdown Message, Runlevel UNINIT Follows
}
if (!defined("IPS_KERNELMESSAGE")) {
    // --- KERNEL
    define('IPS_KERNELMESSAGE', IPS_BASE + 100);           //Kernel Message
    define('KR_CREATE', IPS_KERNELMESSAGE + 1);            //Kernel is beeing created
    define('KR_INIT', IPS_KERNELMESSAGE + 2);              //Kernel Components are beeing initialised, Modules loaded, Settings read
    define('KR_READY', IPS_KERNELMESSAGE + 3);             //Kernel is ready and running
    define('KR_UNINIT', IPS_KERNELMESSAGE + 4);            //Got Shutdown Message, unloading all stuff
    define('KR_SHUTDOWN', IPS_KERNELMESSAGE + 5);          //Uninit Complete, Destroying Kernel Inteface
}
if (!defined("IPS_LOGMESSAGE")) {
    // --- KERNEL LOGMESSAGE
    define('IPS_LOGMESSAGE', IPS_BASE + 200);              //Logmessage Message
    define('KL_MESSAGE', IPS_LOGMESSAGE + 1);              //Normal Message                      | FG: Black | BG: White  | STLYE : NONE
    define('KL_SUCCESS', IPS_LOGMESSAGE + 2);              //Success Message                     | FG: Black | BG: Green  | STYLE : NONE
    define('KL_NOTIFY', IPS_LOGMESSAGE + 3);               //Notiy about Changes                 | FG: Black | BG: Blue   | STLYE : NONE
    define('KL_WARNING', IPS_LOGMESSAGE + 4);              //Warnings                            | FG: Black | BG: Yellow | STLYE : NONE
    define('KL_ERROR', IPS_LOGMESSAGE + 5);                //Error Message                       | FG: Black | BG: Red    | STLYE : BOLD
    define('KL_DEBUG', IPS_LOGMESSAGE + 6);                //Debug Informations + Script Results | FG: Grey  | BG: White  | STLYE : NONE
    define('KL_CUSTOM', IPS_LOGMESSAGE + 7);               //User Message                        | FG: Black | BG: White  | STLYE : NONE
}
if (!defined("IPS_MODULEMESSAGE")) {
    // --- MODULE LOADER
    define('IPS_MODULEMESSAGE', IPS_BASE + 300);           //ModuleLoader Message
    define('ML_LOAD', IPS_MODULEMESSAGE + 1);              //Module loaded
    define('ML_UNLOAD', IPS_MODULEMESSAGE + 2);            //Module unloaded
}
if (!defined("IPS_OBJECTMESSAGE")) {
    // --- OBJECT MANAGER
    define('IPS_OBJECTMESSAGE', IPS_BASE + 400);
    define('OM_REGISTER', IPS_OBJECTMESSAGE + 1);          //Object was registered
    define('OM_UNREGISTER', IPS_OBJECTMESSAGE + 2);        //Object was unregistered
    define('OM_CHANGEPARENT', IPS_OBJECTMESSAGE + 3);      //Parent was Changed
    define('OM_CHANGENAME', IPS_OBJECTMESSAGE + 4);        //Name was Changed
    define('OM_CHANGEINFO', IPS_OBJECTMESSAGE + 5);        //Info was Changed
    define('OM_CHANGETYPE', IPS_OBJECTMESSAGE + 6);        //Type was Changed
    define('OM_CHANGESUMMARY', IPS_OBJECTMESSAGE + 7);     //Summary was Changed
    define('OM_CHANGEPOSITION', IPS_OBJECTMESSAGE + 8);    //Position was Changed
    define('OM_CHANGEREADONLY', IPS_OBJECTMESSAGE + 9);    //ReadOnly was Changed
    define('OM_CHANGEHIDDEN', IPS_OBJECTMESSAGE + 10);     //Hidden was Changed
    define('OM_CHANGEICON', IPS_OBJECTMESSAGE + 11);       //Icon was Changed
    define('OM_CHILDADDED', IPS_OBJECTMESSAGE + 12);       //Child for Object was added
    define('OM_CHILDREMOVED', IPS_OBJECTMESSAGE + 13);     //Child for Object was removed
    define('OM_CHANGEIDENT', IPS_OBJECTMESSAGE + 14);      //Ident was Changed
}
if (!defined("IPS_INSTANCEMESSAGE")) {
    // --- INSTANCE MANAGER
    define('IPS_INSTANCEMESSAGE', IPS_BASE + 500);         //Instance Manager Message
    define('IM_CREATE', IPS_INSTANCEMESSAGE + 1);          //Instance created
    define('IM_DELETE', IPS_INSTANCEMESSAGE + 2);          //Instance deleted
    define('IM_CONNECT', IPS_INSTANCEMESSAGE + 3);         //Instance connectged
    define('IM_DISCONNECT', IPS_INSTANCEMESSAGE + 4);      //Instance disconncted
    define('IM_CHANGESTATUS', IPS_INSTANCEMESSAGE + 5);    //Status was Changed
    define('IM_CHANGESETTINGS', IPS_INSTANCEMESSAGE + 6);  //Settings were Changed
    define('IM_CHANGESEARCH', IPS_INSTANCEMESSAGE + 7);    //Searching was started/stopped
    define('IM_SEARCHUPDATE', IPS_INSTANCEMESSAGE + 8);    //Searching found new results
    define('IM_SEARCHPROGRESS', IPS_INSTANCEMESSAGE + 9);  //Searching progress in %
    define('IM_SEARCHCOMPLETE', IPS_INSTANCEMESSAGE + 10); //Searching is complete
}
if (!defined("IPS_VARIABLEMESSAGE")) {
    // --- VARIABLE MANAGER
    define('IPS_VARIABLEMESSAGE', IPS_BASE + 600);              //Variable Manager Message
    define('VM_CREATE', IPS_VARIABLEMESSAGE + 1);               //Variable Created
    define('VM_DELETE', IPS_VARIABLEMESSAGE + 2);               //Variable Deleted
    define('VM_UPDATE', IPS_VARIABLEMESSAGE + 3);               //On Variable Update
    define('VM_CHANGEPROFILENAME', IPS_VARIABLEMESSAGE + 4);    //On Profile Name Change
    define('VM_CHANGEPROFILEACTION', IPS_VARIABLEMESSAGE + 5);  //On Profile Action Change
}
if (!defined("IPS_SCRIPTMESSAGE")) {
    // --- SCRIPT MANAGER
    define('IPS_SCRIPTMESSAGE', IPS_BASE + 700);           //Script Manager Message
    define('SM_CREATE', IPS_SCRIPTMESSAGE + 1);            //On Script Create
    define('SM_DELETE', IPS_SCRIPTMESSAGE + 2);            //On Script Delete
    define('SM_CHANGEFILE', IPS_SCRIPTMESSAGE + 3);        //On Script File changed
    define('SM_BROKEN', IPS_SCRIPTMESSAGE + 4);            //Script Broken Status changed
}
if (!defined("IPS_EVENTMESSAGE")) {
    // --- EVENT MANAGER
    define('IPS_EVENTMESSAGE', IPS_BASE + 800);             //Event Scripter Message
    define('EM_CREATE', IPS_EVENTMESSAGE + 1);             //On Event Create
    define('EM_DELETE', IPS_EVENTMESSAGE + 2);             //On Event Delete
    define('EM_UPDATE', IPS_EVENTMESSAGE + 3);
    define('EM_CHANGEACTIVE', IPS_EVENTMESSAGE + 4);
    define('EM_CHANGELIMIT', IPS_EVENTMESSAGE + 5);
    define('EM_CHANGESCRIPT', IPS_EVENTMESSAGE + 6);
    define('EM_CHANGETRIGGER', IPS_EVENTMESSAGE + 7);
    define('EM_CHANGETRIGGERVALUE', IPS_EVENTMESSAGE + 8);
    define('EM_CHANGETRIGGEREXECUTION', IPS_EVENTMESSAGE + 9);
    define('EM_CHANGECYCLIC', IPS_EVENTMESSAGE + 10);
    define('EM_CHANGECYCLICDATEFROM', IPS_EVENTMESSAGE + 11);
    define('EM_CHANGECYCLICDATETO', IPS_EVENTMESSAGE + 12);
    define('EM_CHANGECYCLICTIMEFROM', IPS_EVENTMESSAGE + 13);
    define('EM_CHANGECYCLICTIMETO', IPS_EVENTMESSAGE + 14);
}
if (!defined("IPS_MEDIAMESSAGE")) {
    // --- MEDIA MANAGER
    define('IPS_MEDIAMESSAGE', IPS_BASE + 900);           //Media Manager Message
    define('MM_CREATE', IPS_MEDIAMESSAGE + 1);             //On Media Create
    define('MM_DELETE', IPS_MEDIAMESSAGE + 2);             //On Media Delete
    define('MM_CHANGEFILE', IPS_MEDIAMESSAGE + 3);         //On Media File changed
    define('MM_AVAILABLE', IPS_MEDIAMESSAGE + 4);          //Media Available Status changed
    define('MM_UPDATE', IPS_MEDIAMESSAGE + 5);
}
if (!defined("IPS_LINKMESSAGE")) {
    // --- LINK MANAGER
    define('IPS_LINKMESSAGE', IPS_BASE + 1000);           //Link Manager Message
    define('LM_CREATE', IPS_LINKMESSAGE + 1);             //On Link Create
    define('LM_DELETE', IPS_LINKMESSAGE + 2);             //On Link Delete
    define('LM_CHANGETARGET', IPS_LINKMESSAGE + 3);       //On Link TargetID change
}
if (!defined("IPS_FLOWMESSAGE")) {
    // --- DATA HANDLER
    define('IPS_FLOWMESSAGE', IPS_BASE + 1100);             //Data Handler Message
    define('FM_CONNECT', IPS_FLOWMESSAGE + 1);             //On Instance Connect
    define('FM_DISCONNECT', IPS_FLOWMESSAGE + 2);          //On Instance Disconnect
}
if (!defined("IPS_ENGINEMESSAGE")) {
    // --- SCRIPT ENGINE
    define('IPS_ENGINEMESSAGE', IPS_BASE + 1200);           //Script Engine Message
    define('SE_UPDATE', IPS_ENGINEMESSAGE + 1);             //On Library Refresh
    define('SE_EXECUTE', IPS_ENGINEMESSAGE + 2);            //On Script Finished execution
    define('SE_RUNNING', IPS_ENGINEMESSAGE + 3);            //On Script Started execution
}
if (!defined("IPS_PROFILEMESSAGE")) {
    // --- PROFILE POOL
    define('IPS_PROFILEMESSAGE', IPS_BASE + 1300);
    define('PM_CREATE', IPS_PROFILEMESSAGE + 1);
    define('PM_DELETE', IPS_PROFILEMESSAGE + 2);
    define('PM_CHANGETEXT', IPS_PROFILEMESSAGE + 3);
    define('PM_CHANGEVALUES', IPS_PROFILEMESSAGE + 4);
    define('PM_CHANGEDIGITS', IPS_PROFILEMESSAGE + 5);
    define('PM_CHANGEICON', IPS_PROFILEMESSAGE + 6);
    define('PM_ASSOCIATIONADDED', IPS_PROFILEMESSAGE + 7);
    define('PM_ASSOCIATIONREMOVED', IPS_PROFILEMESSAGE + 8);
    define('PM_ASSOCIATIONCHANGED', IPS_PROFILEMESSAGE + 9);
}
if (!defined("IPS_TIMERMESSAGE")) {
    // --- TIMER POOL
    define('IPS_TIMERMESSAGE', IPS_BASE + 1400);            //Timer Pool Message
    define('TM_REGISTER', IPS_TIMERMESSAGE + 1);
    define('TM_UNREGISTER', IPS_TIMERMESSAGE + 2);
    define('TM_SETINTERVAL', IPS_TIMERMESSAGE + 3);
    define('TM_UPDATE', IPS_TIMERMESSAGE + 4);
    define('TM_RUNNING', IPS_TIMERMESSAGE + 5);
}

if (!defined("IS_ACTIVE")) { //Nur wenn Konstanten noch nicht bekannt sind.
    // --- STATUS CODES
    define('IS_SBASE', 100);
    define('IS_CREATING', IS_SBASE + 1); //module is being created
    define('IS_ACTIVE', IS_SBASE + 2); //module created and running
    define('IS_DELETING', IS_SBASE + 3); //module us being deleted
    define('IS_INACTIVE', IS_SBASE + 4); //module is not beeing used
// --- ERROR CODES
    define('IS_EBASE', 200);          //default errorcode
    define('IS_NOTCREATED', IS_EBASE + 1); //instance could not be created
}

if (!defined("vtBoolean")) { //Nur wenn Konstanten noch nicht bekannt sind.
    define('vtBoolean', 0);
    define('vtInteger', 1);
    define('vtFloat', 2);
    define('vtString', 3);
}

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
    const ON = "01";
    const OFF = "00";

    public static $Hertz = array(
        133 => 50,
        197 => 60
    );

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
    const ACK = "00C1";
    const NACK = "00C2";
    const UNKNOW = "00C3";          //	"Command not allowed"
    const SWITCHON = "00D8";
    const JOININGENABLE = "00D9";   //	"Allow nodes to join ACK1"
    const JOININGDISABLE = "00DD";  //	"Allow nodes to join ACK0"
    const SWITCHOFF = "00DE";
    const SUCCESSFUL = "00DF";      //	"Set RTC-Data ACK"
    //                 "00E7";      //	"Set RTC-Data NACK"
    const OUTOFRANGE = "00E1";
    const DISCONNECTED = "00F2";    //	"Reply role changed OK"
    //                  "00F3";     //	"Reply role changed NOK"
    const CONNECTED = "00F4";       //	"Set handle on"
    //                  "00F5";     //	"Set handle off"
    //                  "00F9";     //	"Clear group MAC-Table"
    //          	"00FA";     //	"Fill Switch-schedule"
    //  	 	"00F7";     //	"Request self-removal from network"
    //          	"00F1";     //	"Set broadcast-time interval"
    //                  "00E6";     //	"Set PN"
    //           	"00F8";     //	"Set powerrecording"
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
    //  	 	"00C5";     //	"Exceeding Tableindex"
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
    const SETTIMEACK = "00D7";     //	"Set year, month and flashadress DONE"

    //  	 	"00BD";     //	"Start Light-Calibration started"
    //  	 	"00E9";     //	"Start Pingrun ACK"
    //  	 	"00EA";     //	"Stop Pingrun ACK"
    //  	 	"00DC";     //	"Syncronize NC ACK"
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
     * returns power usage as impulse counters for several different timeframes
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
     * Send chunck of On/Off/StandbyKiller Schedule to Stick
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
     * Pushbuttons
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

    public static $Type = array(
        0 => self::Cricle,
        '00' => self::Cricle,
        '01' => self::Cricle,
        '02' => self::Cricle,
        '03' => self::Switche,
        '04' => self::Switche,
        '05' => self::Sense,
        '06' => self::Scan,
    );

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
 * @version       0.1
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
    public $Command = "";

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
    public $NodeMAC = "";

    /**
     * Payload
     * @access private
     * @var string
     */
    public $Data = "";

    /**
     * Checksumme
     * @access private
     * @var bool
     */
    public $Checksum = null;

    /**
     * Erstellt ein Plugwise_Data Objekt.
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
            if ($NodeMAC2 == Plugwise_Frame::CirclePlusMac) {
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
        $bits = pack("L", $intval);
        return unpack("f", $bits)[1];
    }

    public static function Timestamp2Hex($TimeStamp)
    {
        $date = sprintf('%02X%02X%02X', gmdate("y", $TimeStamp), gmdate("m", $TimeStamp), ((gmdate("j", $TimeStamp) - 1) * 24 + gmdate("G", $TimeStamp)) * 60 + gmdate("i", $TimeStamp));
        $time = sprintf('%02X%02X%02X%02X', gmdate("G", $TimeStamp), gmdate("i", $TimeStamp), gmdate("s", $TimeStamp), gmdate("N", $TimeStamp));
        return $date . 'FFFFFFFF' . $time;
    }

    public static function Hex2Timestamp($TimeString)
    {
        if ($TimeString == 'FFFFFFFF') {
            return;
        }
        if (substr($TimeString, 0, 4) == '0001') {
            return;
        }

        $circle_date = sprintf("%04d-%02d-%02d", hexdec(substr($TimeString, 0, 2)) + 2000, hexdec(substr($TimeString, 2, 2)), (hexdec(substr($TimeString, 4, 4)) / 60 / 24) + 1);
        $time = hexdec(substr($TimeString, 4, 4)) % (60 * 24);
        $hour = intval($time / 60);
        $minutes = $time % 60;
        $circle_time = sprintf(" %02d:%02d", $hour, $minutes);
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
     * Zerlegt den String aus $Data in ein Plugwise_Data-Objekt.
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
            //   Circle MAC       | pulse1 | pulse8 |pulsetotal| pulsehour | ????
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
            //   Circle+ MAC      | gainA    | gainB    | offtot   | offruis
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
            // exsisting | allowed
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
        return json_encode(array("DataID" => '{E7DA1628-D62B-47BF-A834-E5556DD110E7}',
            "Command" => utf8_encode($this->Command),
            "NodeMAC" => utf8_encode($this->NodeMAC),
            "Data" => utf8_encode($this->Data)
        ));
    }

    public function ToJSONStringForDevices()
    {
        return json_encode(array("DataID" => '{CD59EBB4-B313-4ACA-A503-E646CFE0B6FD}',
            "Command" => utf8_encode($this->Command),
            "NodeMAC" => utf8_encode($this->NodeMAC),
            "Data" => utf8_encode($this->Data)
        ));
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
            $buffer .= sprintf("%04X", $this->FrameID);
        }
        $buffer .= $this->NodeMAC . $this->Data;
        $crc16c = 0x0000;  // the crc initial value laut www.maartendamen.com
        $buffer_length = strlen($buffer);
        for ($i = 0; $i < $buffer_length; $i++) {
            $ch = ord($buffer[$i]);
            $crc16c = $this->update_common_crc16c($ch, $crc16c);
        }

        return sprintf("%04X", $crc16c); //strtoupper(str_pad(dechex($crc16c), 4, '0', STR_PAD_LEFT)); //mit nullen auffüllen
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
 * Trait mit Hilfsfunktionen für Variablenprofile.
 */
trait VariableProfile
{

    /**
     * Erstell und konfiguriert ein VariablenProfil für den Typ integer mit Assoziationen
     *
     * @access protected
     * @param string $Name Name des Profils.
     * @param string $Icon Name des Icon.
     * @param string $Prefix Prefix für die Darstellung.
     * @param string $Suffix Suffix für die Darstellung.
     * @param array $Associations Assoziationen der Werte als Array.
     */
    protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations)
    {
        if (sizeof($Associations) === 0) {
            $MinValue = 0;
            $MaxValue = 0;
        } else {
            $MinValue = $Associations[0][0];
            $MaxValue = $Associations[sizeof($Associations) - 1][0];
        }
        $this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
        $old = IPS_GetVariableProfile($Name)["Associations"];
        $OldValues = array_column($old, 'Value');
        foreach ($Associations as $Association) {
            IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
            $OldKey = array_search($Association[0], $OldValues);
            if (!($OldKey === false)) {
                unset($OldValues[$OldKey]);
            }
        }
        foreach ($OldValues as $OldKey => $OldValue) {
            IPS_SetVariableProfileAssociation($Name, $OldValue, '', '', 0);
        }
    }

    /**
     * Erstell und konfiguriert ein VariablenProfil für den Typ integer
     *
     * @access protected
     * @param string $Name Name des Profils.
     * @param string $Icon Name des Icon.
     * @param string $Prefix Prefix für die Darstellung.
     * @param string $Suffix Suffix für die Darstellung.
     * @param int $MinValue Minimaler Wert.
     * @param int $MaxValue Maximaler wert.
     * @param int $StepSize Schrittweite
     */
    protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize)
    {
        $this->RegisterProfile(1, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize);
    }

    /**
     * Erstell und konfiguriert ein VariablenProfil für den Typ float
     *
     * @access protected
     * @param string $Name Name des Profils.
     * @param string $Icon Name des Icon.
     * @param string $Prefix Prefix für die Darstellung.
     * @param string $Suffix Suffix für die Darstellung.
     * @param int $MinValue Minimaler Wert.
     * @param int $MaxValue Maximaler wert.
     * @param int $StepSize Schrittweite
     */
    protected function RegisterProfileFloat($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits)
    {
        $this->RegisterProfile(2, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits);
    }

    /**
     * Erstell und konfiguriert ein VariablenProfil für den Typ float
     *
     * @access protected
     * @param int $VarTyp Typ der Variable
     * @param string $Name Name des Profils.
     * @param string $Icon Name des Icon.
     * @param string $Prefix Prefix für die Darstellung.
     * @param string $Suffix Suffix für die Darstellung.
     * @param int $MinValue Minimaler Wert.
     * @param int $MaxValue Maximaler wert.
     * @param int $StepSize Schrittweite
     */
    protected function RegisterProfile($VarTyp, $Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits = 0)
    {
        if (!IPS_VariableProfileExists($Name)) {
            IPS_CreateVariableProfile($Name, $VarTyp);
        } else {
            $profile = IPS_GetVariableProfile($Name);
            if ($profile['ProfileType'] != $VarTyp) {
                throw new Exception("Variable profile type does not match for profile " . $Name, E_USER_WARNING);
            }
        }

        IPS_SetVariableProfileIcon($Name, $Icon);
        IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
        IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
        if ($VarTyp == vtFloat) {
            IPS_SetVariableProfileDigits($Name, $Digits);
        }
    }

    /**
     * Löscht ein Variablenprofile, sofern es nicht außerhalb dieser Instanz noch verwendet wird.
     * @param string $Name Name des zu löschenden Profils.
     */
    protected function UnregisterProfil(string $Name)
    {
        if (!IPS_VariableProfileExists($Name)) {
            return;
        }
        foreach (IPS_GetVariableList() as $VarID) {
            if (IPS_GetParent($VarID) == $this->InstanceID) {
                continue;
            }
            if (IPS_GetVariable($VarID)['VariableCustomProfile'] == $Name) {
                return;
            }
            if (IPS_GetVariable($VarID)['VariableProfile'] == $Name) {
                return;
            }
        }
        IPS_DeleteVariableProfile($Name);
    }
}

/**
 * Trait für den Datenaustausch zwischen Splitter und Device.
 *
 * @trait
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       0.1
 * @example <b>Ohne</b>
 */
trait Plugwise
{
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
        if (is_a($Data, 'Plugwise_Frame')) {
            /* @var $Data Plugwise_Frame */
            $this->SendDebug($Message . ":Command", Plugwise_Command::ToString($Data->Command), 0);
            if ($Data->FrameID !== -1) {
                $this->SendDebug($Message . ":FrameID", $Data->FrameID, 0);
            }
            if ($Data->NodeMAC !== "") {
                $this->SendDebug($Message . ":NodeMAC", $Data->NodeMAC, 0);
            }
            if ($Data->Data !== "") {
                $this->SendDebug($Message . ":Payload", $Data->Data, 0);
            }
            if ($Data->Checksum !== null) {
                $this->SendDebug($Message . ":Checksum", $Data->Checksum, 0);
            }
        } elseif (is_a($Data, 'Plugwise_Data')) {
            /* @var $Data Plugwise_Data */
            $this->SendDebug($Message . ":Command", Plugwise_Command::ToString($Data->Command), 0);
            if ($Data->NodeMAC !== "") {
                $this->SendDebug($Message . ":NodeMAC", $Data->NodeMAC, 0);
            }
            if ($Data->Data !== "") {
                $this->SendDebug($Message . ":Payload", $Data->Data, 0);
            }
        } elseif (is_array($Data)) {
            foreach ($Data as $Key => $DebugData) {
                $this->SendDebug($Message . ":" . $Key, $DebugData, 0);
            }
        } elseif (is_object($Data)) {
            foreach ($Data as $Key => $DebugData) {
                $this->SendDebug($Message . "." . $Key, $DebugData, 0);
            }
        } elseif (is_bool($Data)) {
            parent::SendDebug($Message, ($Data ? 'TRUE' : 'FALSE'), 0);
        } else {
            parent::SendDebug($Message, $Data, $Format);
        }
    }
}

/**
 * Trait mit Hilfsfunktionen für den Datenaustausch.
 * @property integer $ParentID
 */
trait InstanceStatus
{

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    protected function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->IOChangeState(IS_INACTIVE);
        switch ($Message) {
            case FM_CONNECT:
                $this->RegisterParent();
                if ($this->HasActiveParent()) {
                    $this->IOChangeState(IS_ACTIVE);
                } else {
                    $this->IOChangeState(IS_INACTIVE);
                }
                break;
            case FM_DISCONNECT:
                $this->RegisterParent();
                $this->IOChangeState(IS_INACTIVE);
                break;
            case IM_CHANGESTATUS:
                if ($SenderID == $this->ParentID) {
                    $this->IOChangeState($Data[0]);
                }
                break;
        }
    }

    /**
     * Ermittelt den Parent und verwaltet die Einträge des Parent im MessageSink
     * Ermöglicht es das Statusänderungen des Parent empfangen werden können.
     *
     * @access protected
     * @return int ID des Parent.
     */
    protected function RegisterParent()
    {
        $OldParentId = $this->ParentID;
        $ParentId = @IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($ParentId <> $OldParentId) {
            if ($OldParentId > 0) {
                $this->UnregisterMessage($OldParentId, IM_CHANGESTATUS);
            }
            if ($ParentId > 0) {
                $this->RegisterMessage($ParentId, IM_CHANGESTATUS);
            } else {
                $ParentId = 0;
            }
            $this->ParentID = $ParentId;
        }
        return $ParentId;
    }

    /**
     * Prüft den Parent auf vorhandensein und Status.
     *
     * @access protected
     * @return bool True wenn Parent vorhanden und in Status 102, sonst false.
     */
    protected function HasActiveParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] > 0) {
            $parent = IPS_GetInstance($instance['ConnectionID']);
            if ($parent['InstanceStatus'] == 102) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Trait welcher Objekt-Eigenschaften in den Instance-Buffer schreiben und lesen kann.
 */
trait BufferHelper
{

    /**
     * Wert einer Eigenschaft aus den InstanceBuffer lesen.
     *
     * @access public
     * @param string $name Propertyname
     * @return mixed Value of Name
     */
    public function __get($name)
    {
        if (strpos($name, 'Multi_') === 0) {
            $Lines = "";
            foreach ($this->{"BufferListe_" . $name} as $BufferIndex) {
                $Lines .= $this->{'Part_' . $name . $BufferIndex};
            }
            return unserialize($Lines);
        }
        return unserialize($this->GetBuffer($name));
    }

    /**
     * Wert einer Eigenschaft in den InstanceBuffer schreiben.
     *
     * @access public
     * @param string $name Propertyname
     * @param mixed Value of Name
     */
    public function __set($name, $value)
    {
        $Data = serialize($value);
        if (strpos($name, 'Multi_') === 0) {
            $OldBuffers = $this->{"BufferListe_" . $name};
            if ($OldBuffers == false) {
                $OldBuffers = array();
            }
            $Lines = str_split($Data, 8000);
            foreach ($Lines as $BufferIndex => $BufferLine) {
                $this->{'Part_' . $name . $BufferIndex} = $BufferLine;
            }
            $NewBuffers = array_keys($Lines);
            $this->{"BufferListe_" . $name} = $NewBuffers;
            $DelBuffers = array_diff_key($OldBuffers, $NewBuffers);
            foreach ($DelBuffers as $DelBuffer) {
                $this->{'Part_' . $name . $DelBuffer} = "";
            }
            return;
        }
        $this->SetBuffer($name, $Data);
    }
}

/**
 * Biete Funktionen um Thread-Safe auf Objekte zuzugrifen.
 */
trait Semaphore
{

    /**
     * Versucht eine Semaphore zu setzen und wiederholt dies bei Misserfolg bis zu 100 mal.
     * @param string $ident Ein String der den Lock bezeichnet.
     * @return boolean TRUE bei Erfolg, FALSE bei Misserfolg.
     */
    private function lock($ident)
    {
        for ($i = 0; $i < 100; $i++) {
            if (IPS_SemaphoreEnter(__CLASS__ . '.' . (string) $this->InstanceID . (string) $ident, 1)) {
                return true;
            } else {
                IPS_Sleep(mt_rand(1, 5));
            }
        }
        return false;
    }

    /**
     * Löscht eine Semaphore.
     * @param string $ident Ein String der den Lock bezeichnet.
     */
    private function unlock($ident)
    {
        IPS_SemaphoreLeave(__CLASS__ . '.' . (string) $this->InstanceID . (string) $ident);
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
    protected function SetValueBoolean($Ident, $Value, $Profile = "")
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id == false) {
            $id = $this->RegisterVariableBoolean(str_replace(' ', '', $Ident), $this->Translate($Ident), $Profile);
        }
        SetValueBoolean($id, $Value);
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
    protected function SetValueInteger($Ident, $Value, $Profile = "")
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id == false) {
            $id = $this->RegisterVariableInteger(str_replace(' ', '', $Ident), $this->Translate($Ident), $Profile);
        }
        SetValueInteger($id, $Value);
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
    protected function SetValueFloat($Ident, $Value, $Profile = "")
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id == false) {
            $id = $this->RegisterVariableFloat(str_replace(' ', '', $Ident), $this->Translate($Ident), $Profile);
        }
        SetValueFloat($id, $Value);
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
    protected function SetValueString($Ident, $Value, $Profile = "")
    {
        $id = @$this->GetIDForIdent($Ident);
        if ($id == false) {
            $id = $this->RegisterVariableString(str_replace(' ', '', $Ident), $this->Translate($Ident), $Profile);
        }
        SetValueString($id, $Value);
        return true;
    }
}

/** @} */
