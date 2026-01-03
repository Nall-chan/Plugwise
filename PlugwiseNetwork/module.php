<?php

declare(strict_types=1);

/**
 * @package       Plugwise
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2026 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.00
 *
 */
require_once __DIR__ . '/../libs/Plugwise.php';  // diverse Klassen
eval('declare(strict_types=1);namespace PlugwiseNetwork {?>' . file_get_contents(__DIR__ . '/../libs/helper/VariableHelper.php') . '}');

/**
 * PlugwiseSplitter Klasse für die Kommunikation mit der RF-Stick für das Plugwise-Netzwerk.
 * Enthält den Namespace PLUGWISE.
 * Erweitert IPSModule.
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2026 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.00
 * @example <b>Ohne</b>
 * @property int $ParentID Die InstanzeID des IO-Parent
 * @property int $FrameID
 * @property int $LastFrameID
 * @property \Plugwise\Plugwise_Frame[] $ReplyData
 * @property string $BufferIN
 * @property string $CirclePlusMAC
 * @property string $StickMAC
 * @property string $NetworkID
 * @property array $NewNodes
 * @property array $Nodes
 * @property int $SearchIndex
 * @property int $NetworkState
 * @property int $DisableNetworkJoiningTime
 * @method bool lock(string $ident)
 * @method void unlock(string $ident)
 * @method void SetValueBoolean(string $Ident, bool $value)
 * @method void SetValueInteger(string $Ident, int $value)
 * @method void SetValueString(string $Ident, string $value)
 * @method int RegisterParent()
 * @method bool IORequestAction(string $Ident, mixed $Value)
 * @method void IOMessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data)
 */
class PlugwiseNetwork extends IPSModuleStrict
{
    use \Plugwise\BufferHelper,
        \Plugwise\DebugHelper,
        \PlugwiseNetwork\VariableHelper,
        \Plugwise\Semaphore,
        \Plugwise\InstanceStatus{
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
        $this->ParentID = 0;
        $this->FrameID = 0;
        $this->LastFrameID = 0;
        $this->ReplyData = [];
        $this->BufferIN = '';
        $this->CirclePlusMAC = '';
        $this->StickMAC = '';
        $this->NetworkID = '';
        $this->NewNodes = [];
        $this->Nodes = [];
        $this->SearchIndex = 0;
        $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
        $this->DisableNetworkJoiningTime = 0;
        $this->RegisterTimer(Plugwise\Network\Timer::SearchNodes, 0, 'IPS_RequestAction($_IPS[\'TARGET\'], \'SearchNodes\', \'\');');
        $this->RegisterTimer(Plugwise\Network\Timer::DisableNetworkJoining, 0, 'IPS_RequestAction($_IPS[\'TARGET\'], \'DisableNetworkJoining\', \'\');');
        if (IPS_GetKernelRunlevel() != KR_READY) {
            $this->RegisterMessage(0, IPS_KERNELSTARTED);
        }
    }

    /**
     * ApplyChanges
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
        $this->SetStatus(IS_INACTIVE);
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        $this->ReplyData = [];
        $this->BufferIN = '';
        $this->CirclePlusMAC = '';
        $this->StickMAC = '';
        $this->NetworkID = '';
        $this->NewNodes = [];
        $this->Nodes = [];
        $this->SearchIndex = 0;
        $this->DisableNetworkJoiningTime = 0;
        parent::ApplyChanges();
        $this->RegisterParent();
        $this->RegisterVariableString(
            'NetworkID',
            $this->Translate('Network ID'),
            []
        );
        $this->RegisterVariableBoolean(
            'TimeSync',
            $this->Translate('Circle+ time ok'),
            []
        );
        $this->RegisterVariableBoolean(
            'ScanNetwork',
            $this->Translate('Searching active'),
            []
        );
        $this->RegisterVariableBoolean(
            'JoiningActive',
            $this->Translate('Joining active'),
            [
                'PRESENTATION' => VARIABLE_PRESENTATION_SWITCH
            ]
        );
        $this->EnableAction('JoiningActive');
        $this->RegisterVariableString(
            'Hardware',
            $this->Translate('Hardware'),
            []
        );
        $this->RegisterVariableString(
            'Firmware',
            $this->Translate('Firmware'),
            [
                'PRESENTATION'    => VARIABLE_PRESENTATION_DATE_TIME,
                'TEMPLATE'        => VARIABLE_TEMPLATE_DATE
            ]
        );
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
     * GetCompatibleParents
     *
     * @return string
     */
    public function GetCompatibleParents(): string
    {
        return json_encode(
            [
                'type'     => 'require',
                'moduleIDs'=> [
                    \Plugwise\GUID::SerialPort
                ]
            ]
        );
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
        switch ($Message) {
            case IPS_KERNELSTARTED:
                $this->KernelReady();
                break;
        }
    }

    /**
     * RequestAction
     *
     * @param  string $Ident
     * @param  mixed $Value
     * @return void
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        if ($this->IORequestAction($Ident, $Value)) {
            return;
        }
        switch ($Ident) {
            case 'SearchNodes':
                $this->SearchNodes();
                break;
            case 'DisableNetworkJoining':
                if ($this->DisableNetworkJoiningTime > time()) {
                    $Time = gmdate('i:s', $this->DisableNetworkJoiningTime - time());
                    $this->UpdateFormField('DisableNetworkJoining', 'caption', $this->Translate('Remaining time for searching: ') . $Time);
                } else {
                    $this->UpdateFormField('DisableNetworkJoining', 'caption', $this->Translate('Searching finished'));
                    $this->DisableNetworkJoiningTime = 0;
                    $this->EnableNetworkJoining(false);
                }
                break;
            case 'JoiningActive':
                if (!$this->EnableNetworkJoining((bool) $Value)) {
                    echo $this->Translate('Error');
                }
                break;
            default:
                echo $this->Translate('Invalid Ident');
                break;
        }
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
        if (IPS_InstanceExists($this->ParentID)) {
            $Form['elements'][1]['objectID'] = $this->ParentID;
            $Form['elements'][1]['enabled'] = true;
        }

        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            $Form['actions'][0]['enabled'] = false;
            $Form['actions'][1]['enabled'] = false;
        }

        $Nodes = $this->Nodes;
        $Items = [];
        $InstanceIDListe = $this->GetInstanceList(\Plugwise\GUID::Node);
        foreach ($Nodes as $Node) {
            $InstanceID = array_search($Node, $InstanceIDListe);
            $Items[] = [
                'NodeMAC' => $Node,
                'name'    => $InstanceID === false ? $this->Translate('Not configured in Symcon') : IPS_GetName($InstanceID)
            ];
        }
        $Form['actions'][1]['popup']['items'][0]['values'] = $Items;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
        return json_encode($Form);
    }

    /**
     * GetConfigurationForParent
     *
     * @return void
     */
    public function GetConfigurationForParent(): string
    {
        $parentGUID = IPS_GetInstance($this->ParentID)['ModuleInfo']['ModuleID'];
        if ($parentGUID == \Plugwise\GUID::SerialPort) {
            $Config['StopBits'] = '1';
            $Config['BaudRate'] = '115200';
            $Config['Parity'] = 'None';
            $Config['DataBits'] = '8';
            return json_encode($Config);
        }
        return '[]';
    }

    /**
     * EnableNetworkJoining
     *
     * @param  bool $Value
     * @return bool
     */
    public function EnableNetworkJoining(bool $Value): bool
    {
        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            trigger_error($this->Translate('Network not ready.'), E_USER_NOTICE);
            return false;
        }
        if ($this->NetworkState == \Plugwise\Plugwise_NetworkState::Online) {
            $this->NewNodes = [];
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::EnableJoiningRequest, ($Value ? \Plugwise\Plugwise_Switch::ON : \Plugwise\Plugwise_Switch::OFF));
        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            $this->UpdateFormField('DisableNetworkJoining', 'caption', $this->Translate('Error on start searching'));
            return false;
        }
        $Ok = (substr($Result->Data, 0, 4) == ($Value ? \Plugwise\Plugwise_AckMsg::JOININGENABLE : \Plugwise\Plugwise_AckMsg::JOININGDISABLE));
        if ($Ok) {
            if ($this->NetworkState >= \Plugwise\Plugwise_NetworkState::Online) {
                $this->NetworkState = ($Value ? \Plugwise\Plugwise_NetworkState::OnlineJoining : \Plugwise\Plugwise_NetworkState::Online);
            }
            $this->DisableNetworkJoiningTime = $Value ? time() + 240 : 0;
            $this->UpdateFormField('DisableNetworkJoining', 'caption', $this->Translate('Remaining time for searching: ') . '04:00');
            $this->SetValueBoolean('JoiningActive', $Value);
            $this->SetTimerInterval(\Plugwise\Network\Timer::DisableNetworkJoining, $Value ? 1000 : 0);
        } else {
            $this->UpdateFormField('DisableNetworkJoining', 'caption', $this->Translate('Error on start searching'));
        }
        return $Ok;
    }

    /**
     * RequestJoiningOfNodeEx
     *
     * @param  string $NodeMAC
     * @return bool
     */
    public function UIRequestJoiningOfNode(string $NodeMAC): bool
    {
        $result = $this->RequestJoiningOfNode($NodeMAC);
        if ($result === true) {
            echo $this->Translate('MESSAGE:OK. Node accepted join request.');
            $NewNodes = $this->NewNodes;
            $Items = [];
            foreach ($NewNodes as $NewNode) {
                $Items[] = ['NodeMAC' => $NewNode];
            }
            $this->UpdateFormField('NewNodes', 'values', json_encode($Items));
            return true;
        }
        echo $this->Translate('Join of node failed.');
        return false;
    }

    /**
     * RequestJoiningOfNode
     *
     * @param  string $NodeMAC
     * @return bool
     */
    public function RequestJoiningOfNode(string $NodeMAC): bool
    {
        $Nodes = $this->Nodes;
        $Index = array_search($NodeMAC, $Nodes);
        if ($Index !== false) {
            $NewNodes = $this->NewNodes;
            $Index = array_search($NodeMAC, $NewNodes);
            if ($Index !== false) {
                unset($NewNodes[$Index]);
                $this->NewNodes = $NewNodes;
            }
        }
        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            trigger_error($this->Translate('Network not ready.'), E_USER_NOTICE);
            return false;
        }
        if ($this->NetworkState == \Plugwise\Plugwise_NetworkState::Online) {
            $this->EnableNetworkJoining(true);
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::JoinNodeRequest, '', \Plugwise\Plugwise_Switch::ON . $NodeMAC);
        $Result = $this->Send($PlugwiseData);
        $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
        if ($Result !== true) {
            return false;
        }
        $NewNodes = $this->NewNodes;
        $Index = array_search($NodeMAC, $NewNodes);
        if ($Index === false) { //Direktes anlernen, prüfen ob erreichbar
            $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::Ping, $NodeMAC);
            $Result = $this->Send($PlugwiseData);
            if ($Result === false) {
                return false;
            }
            if (($Result->Command == \Plugwise\Plugwise_Command::AckMsgResponse) && (substr($Result->Data, 0, 4) != \Plugwise\Plugwise_AckMsg::ACK)) {
                return false;
            }
        } else {
            // Node mit Werkseinstellungen antworten mit Frame 65533
            $this->SendQueuePush(65533);
            $Result = $this->WaitForResponse(65533, 30000);
            $this->SendDebug('Response (65533)', $Result, 0);
            if ($Result === false) {
                return false;
            }
            $NewNodes = $this->NewNodes;
            $Index = array_search($NodeMAC, $NewNodes);
            if ($Index !== false) {
                unset($NewNodes[$Index]);
                $this->NewNodes = $NewNodes;
            }
        }
        $Nodes = $this->Nodes;
        $Index = array_search($NodeMAC, $Nodes);
        if ($Index === false) {
            $Nodes[] = $NodeMAC;
        }
        $this->Nodes = $Nodes;
        return true;
    }

    /**
     * RequestExcludeOfNodeEx
     *
     * @param  string $NodeMAC
     * @return bool
     */
    public function UIRequestExcludeOfNode(string $NodeMAC): bool
    {
        $result = $this->RequestExcludeOfNode($NodeMAC);
        if ($result === true) {
            echo $this->Translate('MESSAGE:OK. Node accepted exclude request.');
            return true;
        }
        echo $this->Translate('Exclude of node failed.');
        return false;
    }

    /**
     * RequestExcludeOfNode
     *
     * @param  string $NodeMAC
     * @return bool
     */
    public function RequestExcludeOfNode(string $NodeMAC): bool
    {
        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            trigger_error($this->Translate('Network not ready.'), E_USER_NOTICE);
            return false;
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::RemoveNodeRequest, $this->CirclePlusMAC, $NodeMAC);
        $Result = $this->Send($PlugwiseData);
        $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
        if ($Result === false) {
            return false;
        }
        if (substr($Result->Data, -2) == \Plugwise\Plugwise_Switch::ON) {
            return $this->ResetNode($NodeMAC);
        }
        return false;
    }

    /**
     * ResetNode
     *
     * @param  string $NodeMAC
     * @return bool
     */
    public function ResetNode(string $NodeMAC): bool
    {
        if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
            trigger_error($this->Translate('Network not ready.'), E_USER_NOTICE);
            return false;
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::ResetRequest, $NodeMAC, \Plugwise\Plugwise_Command::QueryCirclePlusRequest);
        $Result = $this->Send($PlugwiseData);
        $this->SendDebug('Response', $Result, 0);
        if ($Result === false) {
            return false;
        }
        if (substr($Result->Data, 0, 4) == \Plugwise\Plugwise_AckMsg::DISCONNECTED) {
            $Nodes = $this->Nodes;
            $Index = array_search($NodeMAC, $Nodes);
            if ($Index !== false) {
                unset($Nodes[$Index]);
                $this->Nodes = $Nodes;
            }
            return true;
        }
        return false;
    }

    /**
     * ConnectCirclePlus
     *
     * @param  string $CirclePlusMac
     * @return array|bool
     */
    public function ConnectCirclePlus(string $CirclePlusMac): array|bool
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::ConnectCirclePlusRequest, '00000000000000000000', $CirclePlusMac);

        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            return false;
        }
        $Values['exists'] = (substr($Result->Data, 0, 2) == '01');
        $Values['allowed'] = (substr($Result->Data, 2, 2) == '01');
        return $Values;
    }

    /**
     * ForwardData
     *
     * @param string $JSONString Ein Plugwise_Frame-Objekt welches als JSONString kodiert ist.
     * @return string
     */
    public function ForwardData(string $JSONString): string
    {
        $this->SendDebug('Forward from Child', $JSONString, 0);
        $Data = json_decode($JSONString);
        if ($Data->DataID == '{E7DA1628-D62B-47BF-A834-E5556DD110E7}') {
            $PlugwiseData = new \Plugwise\Plugwise_Frame($Data, $this->CirclePlusMAC);
            $ret = $this->Send($PlugwiseData);
            //if ($ret !== false) {
            return serialize($ret);
            //}
            //return serialize(false);
        } elseif ($Data->DataID == '{53FBE996-B1E9-45C2-B8DB-5BD6E5E3F94C}') {
            if ($this->NetworkState < \Plugwise\Plugwise_NetworkState::Online) {
                return serialize(false);
            }
            switch (hex2bin($Data->Function)) {
                case 'GetCirclePlusMAC':
                    return serialize($this->CirclePlusMAC);
                case 'ListNodes':
                    return serialize($this->Nodes);
            }
        }
        return serialize(false);
    }

    /**
     * ReceiveData
     *
     * Empfängt Daten vom Parent.
     *
     * @param string $JSONString Das empfangene JSON-kodierte Objekt vom Parent.
     * @return string
     */
    public function ReceiveData(string $JSONString): string
    {
        $data = json_decode($JSONString);
        // Datenstrom zusammenfügen
        $head = $this->BufferIN;
        $Data = $head . hex2bin($data->Buffer);
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
                $this->SendDebug('ReceiveAck (' . $PlugwiseData->FrameID . ')', $PlugwiseData, 0);
                if ($PlugwiseData->Checksum !== true) {
                    $this->SendDebug('ReceiveCRC (' . $PlugwiseData->FrameID . ')', 'Receive CRC ERROR', 0);
                    $this->FrameID = -1;
                    continue;
                }
                $AckData = substr($PlugwiseData->Data, 0, 4);
                $this->SendDebug('ReceiveAck:PayloadText (' . $PlugwiseData->FrameID . ')', \Plugwise\Plugwise_AckMsg::ToString($AckData), 0);
                switch ($AckData) {
                    case \Plugwise\Plugwise_AckMsg::ACK:
                        $this->SendQueuePush($PlugwiseData->FrameID);
                        $this->FrameID = $PlugwiseData->FrameID;
                        break;
                    case \Plugwise\Plugwise_AckMsg::NACK:
                        $this->FrameID = -2;
                        break;
                    default:
                        $this->SendQueueUpdate($PlugwiseData);
                }
                continue;
            }
            if (!$this->SendQueueUpdate($PlugwiseData)) {
                $this->SendDebug('EVENT (' . $PlugwiseData->FrameID . ')', $PlugwiseData, 0);
                $this->Decode($PlugwiseData);
            }
        }
        return '';
    }

    /**
     * KernelReady
     *
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady(): void
    {
        $this->UnregisterMessage(0, IPS_KERNELSTARTED);
        $this->RegisterParent();
        if ($this->HasActiveParent()) {
            $this->StartNetwork();
        }
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
        $this->Nodes = [];
        $this->SearchIndex = 0;
        $this->SendDebug('IOChangeState', $State, 0);
        $this->SetValueBoolean('ScanNetwork', false);
        $this->SetValueBoolean('JoiningActive', false);
        $this->SetValueString('NetworkID', 'no network');
        $this->NetworkState = \Plugwise\Plugwise_NetworkState::StickNotFound;
        if ($State == IS_ACTIVE) {
            $this->StartNetwork();
        } else {
            $this->SetTimerInterval(Plugwise\Network\Timer::SearchNodes, 0);
            $this->SetStatus(IS_INACTIVE);
        }
    }

    /**
     * Decode
     *
     * Dekodiert die empfangenen Pakete und Antworten auf.
     *
     * @param \Plugwise\Plugwise_Frame $PlugwiseData Der zu dekodierende Datensatz als Objekt.
     * @return void
     */
    protected function Decode(\Plugwise\Plugwise_Frame $PlugwiseData): void
    {
        $this->SendDebug('Splitter Decode', $PlugwiseData, 0);
        switch ($PlugwiseData->Command) {
            case \Plugwise\Plugwise_Command::AdvertiseNodeResponse:
                $this->SetValueBoolean('JoiningActive', true);
                $NewNodes = $this->NewNodes;
                if (!in_array($PlugwiseData->NodeMAC, $NewNodes)) {
                    $NewNodes[] = $PlugwiseData->NodeMAC;
                    $this->NewNodes = $NewNodes;
                    $Items = [];
                    foreach ($NewNodes as $NewNode) {
                        $Items[] = ['NodeMAC' => $NewNode];
                    }
                    $this->UpdateFormField('NewNodes', 'values', json_encode($Items));
                }
                break;
            case \Plugwise\Plugwise_Command::AckAssociationResponse:
                $Nodes = $this->Nodes;
                $Index = array_search($PlugwiseData->NodeMAC, $Nodes);
                if ($Index === false) {
                    $Nodes[] = $PlugwiseData->NodeMAC;
                    $this->Nodes = $Nodes;
                }
                $NewNodes = $this->NewNodes;
                $Index = array_search($PlugwiseData->NodeMAC, $NewNodes);
                if ($Index !== false) {
                    unset($NewNodes[$Index]);
                    $this->NewNodes = $NewNodes;
                }
                $this->EnableNetworkJoining(false);
                break;
            default:
                $this->SendDataToChildren($PlugwiseData->ToJSONStringForDevices());
        }
    }

    /**
     * SetStatus
     *
     * @param  int $State
     * @return bool
     */
    protected function SetStatus(int $State): bool
    {
        $OldState = $this->GetStatus();
        if ($OldState == IS_ACTIVE && $State != IS_ACTIVE) {
            $this->ReloadForm();
        }
        if ($OldState != IS_ACTIVE && $State == IS_ACTIVE) {
            $this->ReloadForm();
        }
        return parent::SetStatus($State);
    }

    /**
     * GetInstanceList
     *
     * @param  string $GUID
     * @return array
     */
    private function GetInstanceList(string $GUID): array
    {
        $InstanceIDList = array_flip(array_values(IPS_GetInstanceListByModuleID($GUID)));
        array_walk($InstanceIDList, [$this, 'GetConfigParam'], \Plugwise\Node\Property::NodeMAC);
        return $InstanceIDList;
    }

    /**
     * GetConfigParam
     *
     * @param  mixed $item1
     * @param  int $InstanceID
     * @param  string $ConfigParam
     * @return void
     */
    private function GetConfigParam(mixed &$item1, int $InstanceID, string $ConfigParam): void
    {
        $item1 = IPS_GetProperty($InstanceID, $ConfigParam);
    }

    /**
     * SearchNodes
     *
     * @return bool
     */
    private function SearchNodes(): bool
    {
        $this->SetTimerInterval(Plugwise\Network\Timer::SearchNodes, 0);
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

            $Result = $this->Send($PlugwiseData);
            if ($Result == false) {
                break;
            }
            $NodeMac = substr($Result->Data, 0, -2);
            if ($NodeMac != 'FFFFFFFFFFFFFFFF') {
                $Nodes[] = $NodeMac;
            }
        }
        $this->Nodes = $Nodes;
        if ($index < 63) {
            $this->SearchIndex = $index;
            $this->SetTimerInterval(Plugwise\Network\Timer::SearchNodes, 100);
        } else {
            $this->SearchIndex = 0;
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::Online;
            $this->LogMessage($this->Translate('Finished scanning of the Plugwise network'), KL_NOTIFY);
            $this->SetValueBoolean('ScanNetwork', false);
            if ($this->GetStatus() != IS_ACTIVE) {
                $this->SetStatus(IS_ACTIVE);
            }
        }
        return true;
    }

    /**
     * Send
     *
     * Versendet ein Plugwise-Objekt und empfängt die Antwort.
     *
     * @param \Plugwise\Plugwise_Frame $PlugwiseFrame Das Objekt welches versendet werden soll.
     * @return bool|\Plugwise\Plugwise_Frame Enthält die Antwort auf das Versendete Objekt oder NULL im Fehlerfall.
     */
    private function Send(\Plugwise\Plugwise_Frame $PlugwiseFrame): bool|\Plugwise\Plugwise_Frame
    {
        try {
            $LastFrameId = 'no frame';
            if (!$this->HasActiveParent()) {
                throw new Exception($this->Translate('Instance has no active parent.'), E_USER_NOTICE);
            }
            if (!$this->lock('WaitForStick')) {
                throw new Exception($this->Translate('Send to Stick is locked.'), E_USER_NOTICE);
            }
            $LastFrameId = $this->LastFrameID + 1;
            $DataLine = $PlugwiseFrame->EncodeFrame();
            $JsonString = $this->ToJSONStringForStick($DataLine);
            $this->SendDebug('Send (' . $LastFrameId . ')', $PlugwiseFrame, 0);
            $this->SendDebug('Send (' . $LastFrameId . ') Frame', $DataLine, 0);
            $this->FrameID = 0;
            $this->SendDataToParent($JsonString);
            $Result = $this->WaitForStick();
            $this->LastFrameID = $Result;
            $this->FrameID = 0;
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
            // JoinNodeRequest liefert nur ACK und keine Antwort
            if ($PlugwiseFrame->Command == \Plugwise\Plugwise_Command::JoinNodeRequest) {
                $this->SendQueueRemove($Result);
                return true;
            }
            $ReplyPlugwiseFrame = $this->WaitForResponse($Result);
            if ($ReplyPlugwiseFrame === false) {
                throw new Exception($this->Translate('No answer from network.'), E_USER_NOTICE);
            }
            $this->SendDebug('Response (' . $ReplyPlugwiseFrame->FrameID . ')', $ReplyPlugwiseFrame, 0);
            if ($ReplyPlugwiseFrame->Checksum !== true) {
                throw new Exception($this->Translate('Wrong CRC received.'), E_USER_NOTICE);
            }
            return $ReplyPlugwiseFrame;
        } catch (Exception $ex) {
            $this->SendDebug('Response Error', $ex->getMessage() . ' (' . $LastFrameId . ')', 0);
            trigger_error($ex->getMessage(), $ex->getCode());
        }
        return false;
    }

    /**
     * StartNetwork
     *
     * @return void
     */
    private function StartNetwork(): void
    {
        $this->LogMessage($this->Translate('Starting Plugwise network'), KL_NOTIFY);
        $this->SendDebug('StartNetwork', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);

        switch ($this->NetworkState) {
            case \Plugwise\Plugwise_NetworkState::CirclePlusMissing:
                $this->SetValueBoolean('JoiningActive', false);
                break;
            default:
            case \Plugwise\Plugwise_NetworkState::SearchingNodes:
                $this->NetworkState = \Plugwise\Plugwise_NetworkState::StickNotFound;
                $this->SetTimerInterval(Plugwise\Network\Timer::SearchNodes, 0);
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
                $this->GetStickHardwareInfo();
                $this->SetNetworkTime();
                $this->LogMessage($this->Translate('Plugwise network is up an running'), KL_NOTIFY);
                $this->NewNodes = [];
                $this->SearchNodes();
                break;
            case \Plugwise\Plugwise_NetworkState::ParingCirclePlus:
            case \Plugwise\Plugwise_NetworkState::SearchingCirclePlus:
                break;
        }
    }

    /**
     * InitStick
     *
     * Initialisiert den Stick
     *
     * @return bool
     */
    private function InitStick(): bool
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::StickStatusRequest);
        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            $this->StickMAC = '';
            $this->SetSummary($this->Translate('no Stick'));
            $this->NetworkID = '';
            $this->SetValueString('NetworkID', $this->Translate('no network'));
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::StickNotFound;
            $this->SendDebug('InitStick', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
            $this->LogMessage($this->Translate('RF-Stick not found'), KL_ERROR);
            return false;
        }
        $this->StickMAC = $Result->NodeMAC;
        $this->SetSummary($Result->NodeMAC);
        if (substr($Result->Data, 2, 2) == '00') {
            $this->NetworkID = '';
            $this->SetValueString('NetworkID', $this->Translate('no network'));
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::CirclePlusMissing;
            $this->SendDebug('InitStick', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
            $this->LogMessage($this->Translate('Circle+ not found'), KL_ERROR);
            return false;
        }
        $CirclePlusMAC = '00' . substr($Result->Data, 6, 14);
        $this->CirclePlusMAC = $CirclePlusMAC;
        $this->LogMessage($this->Translate('Circle+ MAC:') . $CirclePlusMAC, KL_NOTIFY);
        $NetworkID = substr($Result->Data, 20, 4);
        $this->NetworkID = $NetworkID;
        $this->SetValueString('NetworkID', $NetworkID);
        $this->LogMessage($this->Translate('Network ID') . ':' . $NetworkID, KL_NOTIFY);
        return true;
    }

    /**
     * VerifyCirclePlus
     *
     * Prüft ob Circle+ online ist
     *
     * @return bool
     */
    private function VerifyCirclePlus(): bool
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::GetConnectedCirclePlus, $this->CirclePlusMAC, '00');
        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            return false;
        }
        if (substr($Result->Data, 0, 4) != \Plugwise\Plugwise_AckMsg::CONNECTED) {
            $this->SetValueString('NetworkID', $this->Translate('no network'));
            $this->NetworkState = \Plugwise\Plugwise_NetworkState::CirclePlusOffline;
            $this->SendDebug('InitStick', \Plugwise\Plugwise_NetworkState::ToString($this->NetworkState), 0);
            $this->LogMessage($this->Translate('Circle+ offline'), KL_WARNING);
            return false;
        }
        return true;
    }

    /**
     * GetStickHardwareInfo
     *
     * @return bool
     */
    private function GetStickHardwareInfo(): bool
    {
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::InfoRequest, $this->StickMAC);
        $Result = $this->Send($PlugwiseData);
        if ($Result == false) {
            return false;
        }
        $Hardware = sprintf('%s-%s-%s', substr($Result->Data, 20, 4), substr($Result->Data, 24, 4), substr($Result->Data, 28, 4));
        $Firmware = (int) hexdec(substr($Result->Data, 32, 8));
        $this->SetValueString('Hardware', $Hardware);
        $this->SetValueInteger('Firmware', $Firmware);
        return true;
    }

    /**
     * SetNetworkTime
     *
     * @return bool
     */
    private function SetNetworkTime(): bool
    {
        $Data = gmdate('siH0Ndmy', time());
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::SetDateTimeRequest, $this->CirclePlusMAC, $Data);
        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            $this->SetValueBoolean('TimeSync', false);
            return false;
        }
        if (substr($Result->Data, 0, 4) != \Plugwise\Plugwise_AckMsg::SUCCESSFUL) {
            $this->SetValueBoolean('TimeSync', false);
            trigger_error($this->Translate('Error on set time in Circle+'), E_USER_NOTICE);
            return false;
        }
        $PlugwiseData = new \Plugwise\Plugwise_Frame(\Plugwise\Plugwise_Command::DateTimeInfoRequest, $this->CirclePlusMAC);
        $Result = $this->Send($PlugwiseData);
        if ($Result === false) {
            $this->SetValueBoolean('TimeSync', false);
            return false;
        }
        if ($Result->Data != $Data) {
            $this->SetValueBoolean('TimeSync', false);
            trigger_error($this->Translate('Error on verify time in Circle+'), E_USER_NOTICE);
            return false;
        }
        $this->SetValueBoolean('TimeSync', true);
        return true;
    }

    /**
     * WaitForStick
     *
     * @return int
     */
    private function WaitForStick(): int
    {
        for ($i = 0; $i < 1000; $i++) {
            if ($this->FrameID === 0) {
                IPS_Sleep(5);
            } else {
                $FrameID = $this->FrameID;
                $this->FrameID = 0;
                return $FrameID;
            }
        }
        return -10;
    }

    /**
     * WaitForResponse
     *
     * Wartet auf eine Antwort.
     *
     * @param  int $FrameID
     * @param  mixed $Seconds
     * @return \Plugwise\Plugwise_Frame|bool Enthält ein Antwort eines \Plugwise\Plugwise_Frame-Objekt mit der Antwort, oder false bei einem Timeout.
     */
    private function WaitForResponse(int $FrameID, ?int $Seconds = null): bool|\Plugwise\Plugwise_Frame
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

    /**
     * Fügt eine Anfrage in die SendQueue ein.
     *
     * @param int $FrameID
     * @return void
     */
    private function SendQueuePush(int $FrameID): void
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
     * @param \Plugwise\Plugwise_Frame $PlugwiseData Das empfangene Plugwise-Objektes.
     * @return bool
     */
    private function SendQueueUpdate(\Plugwise\Plugwise_Frame $PlugwiseData): bool
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
     * @param int $FrameID Der Index des zu löschenden Eintrags.
     * @return void
     */
    private function SendQueueRemove(int $FrameID): void
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
     * ToJSONStringForStick
     *
     * Erzeugt einen, mit der GUID versehenen, JSON-kodierten String zum Versand an den RF-Stick.
     *
     * @param string $GUID Die Interface-GUID welche mit in den JSON-String integriert werden soll.
     * @return string JSON-kodierter String für IPS-Dateninterface.
     */
    private function ToJSONStringForStick(string $Data): string
    {
        $SendData = new stdClass();
        $SendData->DataID = '{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}';
        $SendData->Buffer = bin2hex($Data);
        return json_encode($SendData);
    }
}
