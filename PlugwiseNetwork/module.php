<?php

declare(strict_types=1);

/**
 * @addtogroup plugwise
 * @{
 *
 * @package       Plugwise
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.1
 *
 */
require_once __DIR__ . '/../libs/Plugwise.php';  // diverse Klassen

/**
 * PlugwiseSplitter Klasse für die Kommunikation mit der RF-Stick für das Plugwise-Netzwerk.
 * Enthält den Namespace PLUGWISE.
 * Erweitert IPSModule.
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       1.1
 * @example <b>Ohne</b>
 * @property int $FrameID
 * @property array $ReplyData
 * @property string $BufferIN
 * @property string $CirclePlusMAC
 * @property string $StickMAC
 * @property string $NetworkID
 * @property array $NewNodes
 * @property array $Nodes
 * @property int $SearchIndex
 * @property \Plugwise\Plugwise_NetworkState $NetworkState
 * @method bool lock(string $ident)
 * @method void unlock(string $ident)
 * @method int RegisterParent()
 */
class PlugwiseNetwork extends IPSModule
{
    use \Plugwise\BufferHelper,
        \Plugwise\DebugHelper,
        \Plugwise\InstanceStatus,
        \Plugwise\Semaphore,
        \Plugwise\VariableHelper {
            \Plugwise\InstanceStatus::MessageSink as IOMessageSink;
            \Plugwise\InstanceStatus::RequestAction as IORequestAction;
        }
    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RequireParent('{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}');
        $this->ReplyData = [];
        $this->FrameID = 0;
        $this->BufferIN = '';
        $this->NewNodes = [];
        $this->Nodes = [];
        $this->SearchIndex = 0;
        $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
        $this->RegisterTimer('SearchNodes', 0, 'PLUGWISE_SearchNodes($_IPS["TARGET"]);');
        if (IPS_GetKernelRunlevel() != KR_READY) {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
        }
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Destroy()
    {
        if (IPS_InstanceExists($this->InstanceID)) {
            $this->SetTimerInterval('SearchNodes', 0);
        }
        parent::Destroy();
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        //$this->SetReceiveDataFilter('.*018EF6B5-AB94-40C6-AA53-46943E824ACF.*');
//        $this->RegisterMessage(0, IPS_KERNELSTARTED);
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        $this->ReplyData = [];
        $this->FrameID = 0;
        $this->BufferIN = '';
        parent::ApplyChanges();
        $this->RegisterParent();

        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }

        if ($this->HasActiveParent()) {
            $this->StartNetwork();
        } else {
            $this->SetStatus(IS_INACTIVE);
        }
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        $this->IOMessageSink($TimeStamp, $SenderID, $Message, $Data);

        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
    }

    public function RequestAction($Ident, $Value)
    {
        if ($this->IORequestAction($Ident, $Value)) {
            return true;
        }
        return false;
    }

    public function SearchNodes()
    {
        $this->SetTimerInterval('SearchNodes', 0);
        if ($this->NetworkState >= \Plugwise\Plugwise_NetworkState::Online) {
            $this->Nodes = [];
            $this->SearchIndex = 0;
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::SearchingNodes;
            $this->SetValueBoolean('ScanNetwork', true);
        }
        if ($this->NetworkState != \Plugwise\Plugwise_NetworkState::SearchingNodes) {
            trigger_error('Search for nodes not possible:' . \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), E_USER_NOTICE);
            return false;
        }

        $TargetIndex = $this->SearchIndex + 5;
        if ($TargetIndex > 64) {
            $TargetIndex = 64;
        }
        $Nodes = $this->Nodes;
        for ($index = $this->SearchIndex; $index < $TargetIndex; $index++) {
            $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::AssociatedNodesRequest, $this->CirclePlusMAC, sprintf('%02X', $index));
            /* @var $result \Plugwise\Plugwise_Frame */
            $Result = $this->Send($PlugwiseData);
            if ($Result == null) {
                break;
            }
            $NodeMac = substr($Result->Data, 0, -2);
            $NodeIndex = (hexdec(substr($Result->Data, -2))) + 1;
            if ($NodeMac == 'FFFFFFFFFFFFFFFF') {
                if (array_key_exists($NodeIndex, $Nodes)) {
                    unset($Nodes[$NodeIndex]);
                }
            } else {
                $Nodes[$NodeIndex] = $NodeMac;
            }
        }
        $this->Nodes = $Nodes;
        if ($index < 63) {
            $this->SearchIndex = $index;
            $this->SetTimerInterval('SearchNodes', 100);
        } else {
            $this->SearchIndex = 0;
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
            $this->SetValueBoolean('ScanNetwork', false);
            $this->SetStatus(IS_ACTIVE);
        }
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function GetConfigurationForm()
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $this->SendDebug('GetConfigurationForm', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);

        switch ($this->NetworkState) {
            case \Plugwise\Plugwise_NetworkState::StickNotFound:
                $form['elements'][] = [
                    'type'  => 'Label',
                    'label' => 'No RF-Stick found!'
                ];

                break;
            case \Plugwise\Plugwise_NetworkState::CirclePlusMissing:
                $form['elements'][] = [
                    'type'  => 'Label',
                    'label' => 'No Circle+ associated!'
                ];
                $form['elements'][] = [
                    'type'  => 'Label',
                    'label' => 'Please pair a Circle+ and create a new Plugwise-Network.'
                ];
                /* $form['elements'][] = array(
                  'type' => 'Button',
                  'label' => 'Start paring of Circle+',
                  'onClick' => 'echo "'.$this->Translate('Sorry, not working').'";'
                  ); */
                break;
            case \Plugwise\Plugwise_NetworkState::CirclePlusOffline:
                $form['elements'][] = [
                    'type'  => 'Label',
                    'label' => 'No Circle+ found!'
                ];

                break;
            case \Plugwise\Plugwise_NetworkState::SearchingNodes:
                $form['elements'][] = [
                    'type'  => 'Label',
                    'label' => 'Network discovery is still running. Please wait.'
                ];
                break;
            case \Plugwise\Plugwise_NetworkState::Online:
                $form['elements'][] = [
                    'type'  => 'Label',
                    'label' => 'Enable joining for discovery of new nodes.'
                ];
                $form['elements'][] = [
                    'type'    => 'Button',
                    'label'   => 'Enable joining',
                    'onClick' => 'PLUGWISE_EnableNetworkJoining($id,true);'
                ];
                $form['actions'][] = [
                    'type'  => 'Label',
                    'label' => 'Manually connect new node:'
                ];
                $form['actions'][] = [
                    'type'    => 'ValidationTextBox',
                    'name'    => 'NodeMAC',
                    'caption' => 'Node MAC:'
                ];
                $form['actions'][] = [
                    'type'    => 'Button',
                    'label'   => 'Include node',
                    'onClick' => 'PLUGWISE_RequestJoiningOfNodeEx($id,$NodeMAC);'
                ];

                $form['actions'] = array_merge($form['actions'], $this->GetOnlineForm(), $this->GetJoiningForm());

                break;
            case \Plugwise\Plugwise_NetworkState::OnlineJoining:
                $form['elements'][] = [
                    'type'  => 'Label',
                    'label' => 'Disable network for joining of new nodes.'
                ];
                $form['elements'][] = [
                    'type'    => 'Button',
                    'label'   => 'Disable joining',
                    'onClick' => 'PLUGWISE_EnableNetworkJoining($id,false);'
                ];
                $form['actions'] = array_merge($this->GetOnlineForm(), $this->GetJoiningForm());
                break;
            case \Plugwise\Plugwise_NetworkState::SearchingCirclePlus:
            case \Plugwise\Plugwise_NetworkState::ParingCirclePlus:
            default:
                break;
        }
        $this->SendDebug('FORM', json_encode($form), 0);
        return json_encode($form);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function GetConfigurationForParent()
    {
        $Config['StopBits'] = '1';
        $Config['BaudRate'] = '115200';
        $Config['Parity'] = 'None';
        $Config['DataBits'] = '8';
        return json_encode($Config);
    }

    public function SendDataStick(string $Command, string $Data, string $NodeMAC)
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame($Command, $Data, $NodeMAC);
        $result = $this->Send($PlugwiseData);
        return $result;
    }

    public function EnableNetworkJoining(bool $Value)
    {
        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            trigger_error($this->Translate('Network not ready.'), E_USER_NOTICE);
            return false;
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::EnableJoiningRequest, ($Value ? \Plugwise\Plugwise_Switch::ON : \Plugwise\Plugwise_Switch::OFF));
        $Result = $this->Send($PlugwiseData);
        /* @var $Result \Plugwise\Plugwise_Frame */
        if ($Result === null) {
            return false;
        }
        $Ok = (substr($Result->Data, 0, 4) == ($Value ? \Plugwise\Plugwise_AckMsg::JOININGENABLE : \Plugwise\Plugwise_AckMsg::JOININGDISABLE));
        if ($Ok) {
            if ($this->NetworkState >= \Plugwise\Plugwise_NetworkState::Online) {
                $this->NetworkState = ($Value ? \Plugwise\Plugwise_NetworkState::OnlineJoining : \Plugwise\Plugwise_NetworkState::Online);
            }
            $this->SetValueBoolean('JoiningActive', $Value);
        }
        return $Ok;
    }

    public function RequestJoiningOfNodeEx(string $NodeMAC)
    {
        $result = $this->RequestJoiningOfNode($NodeMAC);
        if ($result === true) {
            echo $this->Translate('OK. Node accepted join request.');
            return true;
        }
        trigger_error($this->Translate('Join of node failed.'), E_USER_NOTICE);
        return false;
    }

    public function RequestJoiningOfNode(string $NodeMAC)
    {
        $Nodes = $this->Nodes;
        $Index = array_search($NodeMAC, $Nodes);
        if ($Index !== false) {
            trigger_error($this->Translate('Node MAC already in network associated.'), E_USER_NOTICE);
            return false;
        }

        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            trigger_error($this->Translate('Network not ready.'), E_USER_NOTICE);
            return false;
        }
        if ($this->NetworkState == \Plugwise\Plugwise_NetworkState::Online) {
            $this->EnableNetworkJoining(true);
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::JoinNodeRequest, '', \Plugwise\Plugwise_Switch::ON . $NodeMAC);

        /* @var $Result bool */
        $Result = $this->Send($PlugwiseData);

        $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
        $this->SetValueBoolean('JoiningActive', false);

        if ($Result !== true) {
            return false;
        }
        $NewNodes = $this->NewNodes;
        $Index = array_search($NodeMAC, $NewNodes);
//        if ($Index !== false)
//        {
//            unset($NewNodes[$Index]);
//            $this->NewNodes = $NewNodes;
//        }
//        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::Ping, $NodeMAC);
//        $Result = $this->Send($PlugwiseData);
//        /* @var $Result \Plugwise\Plugwise_Frame */
//
//        if ($Result === null)
//            return false;
//        if (($Result->Command == \Plugwise\Plugwise_Command::AckMsgResponse) and ( substr($Result->Data, 0, 4) != \Plugwise\Plugwise_AckMsg::ACK))
//            return false;
        if ($Index === false) { //Direktes anlernen, prüfen ob erreichbar
            $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::Ping, $NodeMAC);
            $Result = $this->Send($PlugwiseData);
            /* @var $Result \Plugwise\Plugwise_Frame */
            if ($Result === null) {
                return false;
            }
            if (($Result->Command == \Plugwise\Plugwise_Command::AckMsgResponse) && (substr($Result->Data, 0, 4) != \Plugwise\Plugwise_AckMsg::ACK)) {
                return false;
            }
        } else { // Node mit Werkseinstellungen antworten mit Frame 65533
            //set_time_limit(40);
            $this->SendQueuePush(65533);
            $Result = $this->WaitForResponse(65533, 30000);
            /* @var $Result \Plugwise\Plugwise_Frame */
            $this->SendDebug('Response', $Result, 0);
            if ($Result === false) {
                return false;
            }
            $NewNodes = $this->NewNodes;
            $Index = array_search($NodeMAC, $NewNodes);
            if ($Index !== false) {
                unset($NewNodes[$Index]);
                $this->NewNodes = $NewNodes;
            }
            //Neuer Node einfügen in $this->Nodes
        }
        $this->SearchNodes();
        return true;
    }

    public function RequestExcludeOfNodeEx(string $NodeMAC)
    {
        $result = $this->RequestExcludeOfNode($NodeMAC);
        if ($result === true) {
            echo $this->Translate('OK. Node accepted exclude request.');
            return true;
        }
        trigger_error($this->Translate('Exclude of node failed.'), E_USER_NOTICE);
        return false;
    }

    public function RequestExcludeOfNode(string $NodeMAC)
    {
        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            trigger_error($this->Translate('Network not ready.'), E_USER_NOTICE);
            return false;
        }
        if ($this->NetworkState == \Plugwise\Plugwise_NetworkState::Online) {
            $this->EnableNetworkJoining(true);
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::RemoveNodeRequest, $this->CirclePlusMAC, $NodeMAC);
        /* @var $Result \Plugwise\Plugwise_Frame */
        $Result = $this->Send($PlugwiseData);
        $this->SendDebug('Response', $Result, 0);

        $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
        $this->SetValueBoolean('JoiningActive', false);

        if ($Result === null) {
            return false;
        }
        if (substr($Result->Data, -2) == \Plugwise\Plugwise_Switch::ON) {
            $Nodes = $this->Nodes;
            $Index = array_search($NodeMAC, $Nodes);
            if ($Index !== false) {
                unset($Nodes[$Index]);
                $this->Nodes = $Nodes;
            }
            return $this->ResetNode($NodeMAC);
        }
        return false;
    }

    public function ResetNode(string $NodeMAC)
    {
        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            trigger_error($this->Translate('Network not ready.'), E_USER_NOTICE);
            return false;
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::ResetRequest, $NodeMAC, '0001');
        /* @var $Result \Plugwise\Plugwise_Frame */
        $Result = $this->Send($PlugwiseData);
        $this->SendDebug('Response', $Result, 0);

        if ($Result === null) {
            return false;
        }
        if (substr($Result->Data, 0, 4) == \Plugwise\Plugwise_AckMsg::DISCONNECTED) {
            return true;
        }
        return false;
    }

    /*
      public function QueryCirclePlus()
      {
      $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::QueryCirclePlusRequest);

      $Result = $this->Send($PlugwiseData);
      if ($Result === NULL)
      {

      }
      //          2|                18|                34|                 50|                66|    70|
      //     XX    | 000D6F0000B1B64B | 440D6F0000B1B64B | 440D6F0000B1B64B  | 440D6F0000B1B64B | 1234 | xx
      //     0F    | FFFFFFFFFFFFFFFF | 6B0D6F0002588136 | FFFFFFFFFFFFFFFF  | 6B0D6F0002588136 | 2F6B | 01
      //   channel |  Circle+ MAC     |   PANID-long     | unique_network_id | new_node_mac_id  | PANID | ??
      $Values['Channel'] = hexdec(substr($Result->Data, 0, 2));
      $Values['Stick MAC'] = substr($Result->Data, 2, 16);
      $Values['PanID long'] = substr($Result->Data, 18, 16);
      $Values['Unique Network ID'] = substr($Result->Data, 34, 16);
      $Values['new_node_mac_id'] = substr($Result->Data, 50, 16);
      $Values['PanID'] = substr($Result->Data, 66, 4);
      return $Values;
      } */
    public function ConnectCirclePlus(string $CirclePlusMac)
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::ConnectCirclePlusRequest, '00000000000000000000', $CirclePlusMac);
        /* @var $Result \Plugwise\Plugwise_Frame */
        $Result = $this->Send($PlugwiseData);
        if ($Result === null) {
        }
        $Values['exists'] = (substr($Result->Data, 0, 2) == '01');
        $Values['allowed'] = (substr($Result->Data, 2, 2) == '01');
        return $Values;
    }

    ################## PUBLIC
    ################## DATAPOINTS DEVICE
    /**
     * Interne Funktion des SDK. Nimmt Daten von Children entgegen und sendet Diese weiter.
     *
     * @access public
     * @param string $JSONString Ein Plugwise_Frame-Objekt welches als JSONString kodiert ist.
     * @result bool true wenn Daten gesendet werden konnten, sonst false.
     */
    public function ForwardData($JSONString)
    {
        $this->SendDebug('Forward from Child', $JSONString, 0);
        $Data = json_decode($JSONString);
        if ($Data->DataID == '{E7DA1628-D62B-47BF-A834-E5556DD110E7}') {
            $PlugwiseData = new \Plugwise\Plugwise_Frame($Data, $this->CirclePlusMAC);
            $ret = $this->Send($PlugwiseData);
            if (!is_null($ret)) {
                return serialize($ret);
            }
            return false;
        } elseif ($Data->DataID == '{53FBE996-B1E9-45C2-B8DB-5BD6E5E3F94C}') {
            if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
                trigger_error($this->Translate('Network not ready. Try again later.'), E_USER_NOTICE);
                return false;
            }
            switch (utf8_decode($Data->Function)) {
                case 'GetCirclePlusMAC':
                    return serialize($this->CirclePlusMAC);
                case 'ListNodes':
                    return serialize($this->Nodes);
            }
        }
        return false;
    }

    ################## DATAPOINTS PARENT
    /**
     * Empfängt Daten vom Parent.
     *
     * @access public
     * @param string $JSONString Das empfangene JSON-kodierte Objekt vom Parent.
     * @result bool True wenn Daten verarbeitet wurden, sonst false.
     */
    public function ReceiveData($JSONString)
    {
        $data = json_decode($JSONString);

        // Datenstrom zusammenfügen
        $head = $this->BufferIN;
        $Data = $head . utf8_decode($data->Buffer);
        // Stream in einzelne Pakete schneiden
        $Lines = explode(chr(0x0D) . chr(0x0A), $Data);
        $tail = array_pop($Lines);
        $this->BufferIN = $tail;
        foreach ($Lines as $Line) {
            $Start = strpos($Line, "\x05\x05\x03\x03");
            if ($Start === false) {
                $this->SendDebug('Receive Frame invalid', $Line, 0);
                continue;
            }
            $this->SendDebug('Receive Frame', $Line, 0);

            $Line = substr($Line, $Start + 4);
            $this->SendDebug('ReceiveRaw', $Line, 0);
            $PlugwiseData = new \Plugwise\Plugwise_Frame();
            $PlugwiseData->DecodeFrame($Line);
            if ($PlugwiseData->Command === \Plugwise\Plugwise_Command::AckMsgResponse) {
                if ($this->FrameID === 0) {
                    $this->SendDebug('ReceiveAck', $PlugwiseData, 0);

                    if ($PlugwiseData->Checksum !== true) {
                        $this->SendDebug('Receive', 'Receive CRC ERROR', 0);
                        $this->FrameID = -1;
                        continue;
                    }
                    $this->SendDebug('Receive', \Plugwise\Plugwise_AckMsg::ToString($PlugwiseData->Data), 0);

                    switch ($PlugwiseData->Data) {
                        case \Plugwise\Plugwise_AckMsg::ACK:
                            $this->SendQueuePush($PlugwiseData->FrameID);
                            $this->FrameID = $PlugwiseData->FrameID;
                            break;
                        case \Plugwise\Plugwise_AckMsg::NACK:
                            $this->FrameID = -2;
                            break;
                        case \Plugwise\Plugwise_AckMsg::OUTOFRANGE:
                            $this->FrameID = -3;
                            break;
                        case \Plugwise\Plugwise_AckMsg::UNKNOW:
                            $this->FrameID = -4;
                            break;
                        default:
                            $this->FrameID = -10;
                            break;
                    }
                } else {
                    $this->SendDebug('Receive', $PlugwiseData, 0);

                    $this->SendQueueUpdate($PlugwiseData);
                }
                continue;
            }
            if (!$this->SendQueueUpdate($PlugwiseData)) {
                $this->SendDebug('EVENT', $PlugwiseData, 0);
                $this->Decode($PlugwiseData);
            }
        }
        return true;
    }
    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady()
    {
        $this->UnregisterMessage(0, IPS_KERNELSTARTED);
        $this->RegisterParent();
        if ($this->HasActiveParent()) {
            $this->StartNetwork();
        }
    }

    /**
     * Wird ausgeführt wenn sich der Status vom Parent ändert.
     * @access protected
     */
    protected function IOChangeState($State)
    {
        $this->SendDebug('IOChangeState', $State, 0);

        if ($State == IS_ACTIVE) {
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::StickNotFound;
            $this->SendDebug('IOChangeState', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
            $this->StartNetwork();
        } else {
            $this->SetTimerInterval('SearchNodes', 0);
            $this->Nodes = [];
            $this->SearchIndex = 0;
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::StickNotFound;
            $this->SetValueBoolean('ScanNetwork', false);
            $this->SetValueBoolean('JoiningActive', false);
            $this->SetValueString('NetworkID', 'no Network');
            $this->SetStatus(IS_INACTIVE);
        }
    }

    /**
     * Dekodiert die empfangenen Pakete und Antworten auf.
     *
     * @access protected
     * @param \Plugwise\Plugwise_Frame $PlugwiseData Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode(\Plugwise\Plugwise_Frame $PlugwiseData)
    {
        $this->SendDebug('Splitter Decode', $PlugwiseData, 0);

        switch ($PlugwiseData->Command) {
            case \Plugwise\Plugwise_Command::AdvertiseNodeResponse:
                $NewNodes = $this->NewNodes;
                if (!in_array($PlugwiseData->NodeMAC, $NewNodes)) {
                    $NewNodes[] = $PlugwiseData->NodeMAC;
                    $this->NewNodes = $NewNodes;
                }
                break;
            case \Plugwise\Plugwise_Command::AckAssociationResponse:
                $this->SearchNodes();
                break;
            default:
                $this->SendDataToChildren($PlugwiseData->ToJSONStringForDevices());
        }
    }

    /**
     * Versendet ein Plugwise-Objekt und empfängt die Antwort.
     *
     * @access protected
     * @param \Plugwise\Plugwise_Frame $PlugwiseFrame Das Objekt welches versendet werden soll.
     * @result \Plugwise\Plugwise_Frame Enthält die Antwort auf das Versendete Objekt oder NULL im Fehlerfall.
     */
    protected function Send(\Plugwise\Plugwise_Frame $PlugwiseFrame)
    {
        try {
            if (!$this->HasActiveParent()) {
                throw new Exception($this->Translate('Instance has no active parent.'), E_USER_NOTICE);
            }
            if (!$this->lock('WaitForStick')) {
                throw new Exception($this->Translate('Send to Stick is locked.'), E_USER_NOTICE);
            }
            $DataLine = $PlugwiseFrame->EncodeFrame();
            $JsonString = $this->ToJSONStringForStick($DataLine);
            $this->SendDebug('Send', $PlugwiseFrame, 0);
            $this->SendDebug('Send Frame', $DataLine, 0);
            $this->FrameID = 0;

            $this->SendDataToParent($JsonString);
            $Result = $this->WaitForStick();
            $this->unlock('WaitForStick');
            if ($Result === -10) { // Timeout
                throw new Exception($this->Translate('Stick did not response.'), E_USER_NOTICE);
            } elseif ($Result === -4) { // unknown
                throw new Exception($this->Translate('Stick response unknown error.'), E_USER_NOTICE);
            } elseif ($Result === -3) { // OutOfRange
                throw new Exception($this->Translate('Stick response OutOfRange.'), E_USER_NOTICE);
            } elseif ($Result === -2) { // NACK
                throw new Exception($this->Translate('Stick send NACK.'), E_USER_NOTICE);
            } elseif ($Result === -1) { // CRC Error
                throw new Exception($this->Translate('Wrong CRC received.'), E_USER_NOTICE);
            }

            if ($PlugwiseFrame->Command == \Plugwise\Plugwise_Command::JoinNodeRequest) {
                return true;
            }

            /* @var $ReplyPlugwiseFrame \Plugwise\Plugwise_Frame */
            $ReplyPlugwiseFrame = $this->WaitForResponse($Result);

            //$this->SendDebug('Receive', 'ACK', 0);

            if ($ReplyPlugwiseFrame === false) {
                throw new Exception($this->Translate('No answer from Network.'), E_USER_NOTICE);
            }
            $this->SendDebug('Response', $ReplyPlugwiseFrame, 0);
            if ($ReplyPlugwiseFrame->Checksum !== true) {
                throw new Exception($this->Translate('Wrong CRC received.'), E_USER_NOTICE);
            }
            return $ReplyPlugwiseFrame;
        } catch (Exception $ex) {
            $this->SendDebug('Receive', $ex->getMessage(), 0);
            trigger_error($ex->getMessage(), $ex->getCode());
        }
        return null;
    }

    private function StartNetwork()
    {
        $this->LogMessage('Start Plugwise Network', KL_NOTIFY);
        $this->SendDebug('StartNetwork', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);

        switch ($this->NetworkState) {
            case \Plugwise\Plugwise_NetworkState::CirclePlusMissing:
                $this->SetValueBoolean('JoiningActive', false);
                break;
            default:
            case \Plugwise\Plugwise_NetworkState::SearchingNodes:
                $this->NetworkState = \Plugwise\Plugwise_NetworkState::StickNotFound;
                $this->SetTimerInterval('SearchNodes', 0);
                // SearchingNodes disables Timer and continue with InitStick
                // No break. Add additional comment above this line if intentional
            case \Plugwise\Plugwise_NetworkState::CirclePlusOffline:
            case \Plugwise\Plugwise_NetworkState::StickNotFound:
            case \Plugwise\Plugwise_NetworkState::Online:
                $this->SetValueBoolean('JoiningActive', false);
                if (!$this->InitStick()) {
                    $this->SetStatus(IS_EBASE + 2 + $this->NetworkState);
                    $this->SendDebug('NewNetworkState', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
                    return;
                }
                if (!$this->VerifyCirclePlus()) {
                    $this->SetStatus(IS_EBASE + 2 + $this->NetworkState);
                    $this->SendDebug('NewNetworkState', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
                    return;
                }
                $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
                $this->SendDebug('NewNetworkState', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
                $this->GetStickHardwareInfo($this->StickMAC);
                $this->SetNetworkTime();
                $this->NewNodes = [];
                $this->SearchNodes();
                $this->LogMessage('Plugwise Network is up an running', KL_NOTIFY);
                break;
            case \Plugwise\Plugwise_NetworkState::ParingCirclePlus:
            case \Plugwise\Plugwise_NetworkState::SearchingCirclePlus:
                break;
        }
    }

    private function GetOnlineForm()
    {
        $form[] = [
            'type'  => 'Label',
            'label' => '----------------------------------------------------------------------------------------------------------------------------------'
        ];

        $Nodes = $this->Nodes;
        if (count($Nodes) > 0) {
            $form[] = [
                'type'  => 'Label',
                'label' => 'This nodes are associated, and can be excluded from the network:'
            ];
            $items = [];
            foreach ($Nodes as $Index => $Node) {
                $items[] = ['Index' => $Index, 'NodeMAC' => $Node];
            }
            $form[] = [
                'type'     => 'List',
                'name'     => 'NodesOnline',
                'rowCount' => 7,
                'add'      => false,
                'delete'   => false,
                'sort'     => [
                    'column'    => 'Index',
                    'direction' => 'ascending'
                ],
                'columns'  => [
                    ['label'    => 'Index',
                        'name'  => 'Index',
                        'width' => '60px'
                    ],
                    ['label'    => 'Node MAC',
                        'name'  => 'NodeMAC',
                        'width' => 'auto'
                    ]
                ],
                'values'   => $items
            ];
            $form[] = [
                'type'    => 'Button',
                'label'   => 'Exclude node from network',
                'onClick' => 'PLUGWISE_RequestExcludeOfNodeEx($id,$NodesOnline["NodeMAC"]);'
            ];
        } else {
            $form[] = [
                'type'  => 'Label',
                'label' => 'No nodes found.'
            ];
        }
        return $form;
    }

    private function GetJoiningForm()
    {
        $form[] = [
            'type'  => 'Label',
            'label' => '----------------------------------------------------------------------------------------------------------------------------------'
        ];

        $NewNodes = $this->NewNodes;
        if (count($NewNodes) > 0) {
            $form[] = [
                'type'  => 'Label',
                'label' => 'This nodes can be added to the network:'
            ];
            $items = [];
            foreach ($NewNodes as $NewNode) {
                $items[] = ['NodeMAC' => $NewNode];
            }
            $form[] = [
                'type'     => 'List',
                'name'     => 'nodes',
                'rowCount' => 7,
                'add'      => false,
                'delete'   => false,
                'sort'     => [
                    'column'    => 'NodeMAC',
                    'direction' => 'ascending'
                ],
                'columns'  => [
                    ['label'    => 'Node MAC',
                        'name'  => 'NodeMAC',
                        'width' => 'auto'
                    ]
                ],
                'values'   => $items
            ];
            $form[] = [
                'type'    => 'Button',
                'label'   => 'Include node to network',
                'onClick' => 'PLUGWISE_RequestJoiningOfNodeEx($id,$nodes["NodeMAC"]);'
            ];
        } else {
            $form[] = [
                'type'  => 'Label',
                'label' => 'No unconfigured nodes found.'
            ];
        }
        return $form;
    }

    ################## PRIVATE
    /**
     * Initialisiert den Stick
     */
    private function InitStick()
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::StickStatusRequest);

        $Result = $this->Send($PlugwiseData);
        /* @var $Result Plugwise_Frame */
        if ($Result === null) {
            $this->StickMAC = '';
            $this->SetSummary($this->Translate('no Stick'));
            $this->NetworkID = '';
            $this->SetValueString('NetworkID', $this->Translate('no Network'));
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::StickNotFound;
            $this->SendDebug('InitStick', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
            $this->LogMessage('Stick not found', KL_ERROR);
            return false;
        }
        $this->StickMAC = $Result->NodeMAC;
        $this->SetSummary($Result->NodeMAC);
        if (substr($Result->Data, 2, 2) == '00') {
            $this->NetworkID = '';
            $this->SetValueString('NetworkID', $this->Translate('no Network'));
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::CirclePlusMissing;
            $this->SendDebug('InitStick', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
            $this->LogMessage('Circle+ not found', KL_ERROR);
            return false;
        }
        $this->CirclePlusMAC = '00' . substr($Result->Data, 6, 14);
        $NetworkID = substr($Result->Data, 20, 4);
        $this->NetworkID = $NetworkID;
        $this->SetValueString('NetworkID', $NetworkID);
        $this->LogMessage('NetworkID:' . $NetworkID, KL_NOTIFY);
        return true;
    }

    /**
     * Prüft ob Circle+ online
     */
    private function VerifyCirclePlus()
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::GetConnectedCirclePlus, $this->CirclePlusMAC, '00');
        $Result = $this->Send($PlugwiseData);

        /* @var $Result \Plugwise\Plugwise_Frame */
        if ($Result === null) {
            return false;
        }
        if (substr($Result->Data, 0, 4) != \Plugwise\Plugwise_AckMsg::CONNECTED) {
            $this->SetValueString('NetworkID', $this->Translate('no Network'));
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::CirclePlusOffline;
            $this->SendDebug('InitStick', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
            $this->LogMessage('Circle+ is offline', KL_WARNING);
            return false;
        }
        $CirclePlusMAC = substr($Result->Data, 4);
        $this->CirclePlusMAC = $CirclePlusMAC;
        $this->LogMessage('Circle+ MAC:' . $CirclePlusMAC, KL_NOTIFY);
        return true;
    }

    private function GetStickHardwareInfo()
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::InfoRequest, $this->StickMAC);
        /* @var $Result \Plugwise\Plugwise_Frame */
        $Result = $this->Send($PlugwiseData);
        if ($Result === null) {
            return false;
        }
        $Hardware = sprintf('%s-%s-%s', substr($Result->Data, 20, 4), substr($Result->Data, 24, 4), substr($Result->Data, 28, 4));
        $Firmware = intval(hexdec(substr($Result->Data, 32, 8)));
        $this->SetValueString('Hardware', $Hardware);
        $this->SetValueInteger('Firmware', $Firmware, '~UnixTimestampDate');
        return true;
        //$Result = $this->GetStatus();
        //
        //                 16|              24|        32|   34|  36|              48|              56| 58
        // |000D6F0000994CAA | 0F | 08 | 595B | 00044480 | 80  | 18 | 5653.9070.1402 | 4CCEC22A       | 02
        // |  Circle+ MAC    |year|mon |min   | curr_log |state| HZ | HW1 .HW2 .HW3  | Firmware d/m/y |Typ
        //                     11   0A   3C98   0004ADE8   01    85   6539 0700 7326   4E0843A9         01
        //                     00   00   0000   00000000   00    80   6539 0700 8512   4E0842BB         00  //Stick
        //  000D6F0002588136   11   0A   52D2   00044038   01    85   6539 0700 7326   4E0843A9         01 //Circle
        //                     00010001 000515C801850000044001074E0844C202
    }

    private function SetNetworkTime()
    {
        $Data = gmdate('siH0Ndmy', time());
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::SetDateTimeRequest, $this->CirclePlusMAC, $Data);
        /* @var $Result \Plugwise\Plugwise_Frame */
        $Result = $this->Send($PlugwiseData);
        if ($Result === null) {
            $this->SetValueBoolean('TimeSync', false);
            return false;
        }
        // todo
        // prüfen ?
        // ($Result->Command == \Plugwise\Plugwise_Command::AckMsgResponse)
        if (substr($Result->Data, 0, 4) != \Plugwise\Plugwise_AckMsg::SUCCESSFUL) {
            $this->SetValueBoolean('TimeSync', false);
            trigger_error($this->Translate('Error on set time in Circle+'), E_USER_NOTICE);
            return false;
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::DateTimeInfoRequest, $this->CirclePlusMAC);
        $Result = $this->Send($PlugwiseData);
        if ($Result === null) {
            $this->SetValueBoolean('TimeSync', false);
            return false;
        }
        if ($Result->Data != $Data) {
            $this->SetValueBoolean('TimeSync', false);
            trigger_error($this->Translate('Error on verify time in Circle+'), E_USER_NOTICE);
            return false;
        }
        // 25 47 17  07   15  10 17
        // 08 48 17  07   15  10 17
        // s |i |h  |dow | d |m |y
        $this->SetValueBoolean('TimeSync', true);
        return true;
    }

    private function WaitForStick()
    {
        for ($i = 0; $i < 1000; $i++) {
            if ($this->FrameID === 0) {
                IPS_Sleep(5);
            } else {
                return $this->FrameID;
            }
        }
        return -10;
    }

    /**
     * Wartet auf eine Antwort.
     *
     * @access private
     * @result \Plugwise\Plugwise_Data|boolean Enthält ein Antwort eines \Plugwise\Plugwise_Data-Objekt mit der Antwort, oder false bei einem Timeout.
     */
    private function WaitForResponse(int $FrameID, int $Seconds = null)
    {
        if ($Seconds == null) {
            $Seconds = 5000;
        }
        $Slot = $Seconds / 5;
        for ($i = 0; $i < $Slot; $i++) {
            $Buffer = $this->ReplyData;
            if (!array_key_exists($FrameID, $Buffer)) {
                return false;
            }
            if (is_a($Buffer[$FrameID], 'Plugwise\Plugwise_Frame')) {
                $this->SendQueueRemove($FrameID);
                return $Buffer[$FrameID];
            }
            IPS_Sleep(5);
        }

        $this->SendQueueRemove($FrameID);
        return false;
    }

    ################## SEND QUEUE
    /**
     * Fügt eine Anfrage in die SendQueue ein.
     *
     * @access private
     * @param int $FrameID
     */
    private function SendQueuePush(int $FrameID)
    {
        if (!$this->lock('ReplyData')) {
            throw new Exception('ReplyData is locked', E_USER_NOTICE);
        }

        $Buffer = $this->ReplyData;
        $Buffer[$FrameID] = null;
        $this->ReplyData = $Buffer;
        $this->unlock('ReplyData');
    }

    /**
     * Fügt eine Antwort in die SendQueue ein.
     *
     * @access private
     * @param \Plugwise\Plugwise_Frame $PlugwiseData Das empfangene Plugwise-Objektes.
     */
    private function SendQueueUpdate(\Plugwise\Plugwise_Frame $PlugwiseData)
    {
        if (!$this->lock('ReplyData')) {
            throw new Exception('ReplyData is locked', E_USER_NOTICE);
        }

        $Buffer = $this->ReplyData;
        if (array_key_exists($PlugwiseData->FrameID, $Buffer)) {
            $Buffer[$PlugwiseData->FrameID] = $PlugwiseData;
            $this->ReplyData = $Buffer;
            $this->unlock('ReplyData');
            return true;
        }

        $this->unlock('ReplyData');
        return false;
    }

    /**
     * Löscht einen Eintrag aus der SendQueue.
     *
     * @access private
     * @param int $FrameID Der Index des zu löschenden Eintrags.
     */
    private function SendQueueRemove(int $FrameID)
    {
        if (!$this->lock('ReplyData')) {
            throw new Exception('ReplyData is locked', E_USER_NOTICE);
        }

        $Buffer = $this->ReplyData;
        unset($Buffer[$FrameID]);
        $this->ReplyData = $Buffer;
        $this->unlock('ReplyData');
    }

    /**
     * Erzeugt einen, mit der GUID versehenen, JSON-kodierten String zum versand an den RF-Stick.
     *
     * @access public
     * @param string $GUID Die Interface-GUID welche mit in den JSON-String integriert werden soll.
     * @return string JSON-kodierter String für IPS-Dateninterface.
     */
    private function ToJSONStringForStick($Data)
    {
        $SendData = new stdClass();
        $SendData->DataID = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
        $SendData->Buffer = utf8_encode($Data);
        return json_encode($SendData);
    }
}

/** @} */
