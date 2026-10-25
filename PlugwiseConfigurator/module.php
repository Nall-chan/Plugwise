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

/**
 * PlugwiseConfigurator Klasse für den Konfigurator eines Plugwise-Netzwerk.
 * Erweitert IPSModule.
 *
 * @package       Plugwise
 * @author        Michael Tröger <nall4chan@gmail.com>
 * @copyright     2017-2026 Michael Tröger
 * @license       Custom Non-Commercial Share-Alike License (see LICENSE)
 * @version       2.10
 * @property int $ParentID Die InstanzeID des IO-Parent
 * @method int RegisterParent()
 * @method bool IORequestAction(string $Ident, mixed $Value)
 * @method void IOMessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data)
 */
class PlugwiseConfigurator extends IPSModuleStrict
{
    use \Plugwise\BufferHelper;
    use \Plugwise\DebugHelper;
    use \Plugwise\FormHelper;
    use \Plugwise\InstanceStatus{
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
    }

    /**
     * ApplyChanges
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
        $this->ParentID = 0;
        parent::ApplyChanges();
        $this->RegisterMessage($this->InstanceID, FM_CONNECT);
        $this->RegisterMessage($this->InstanceID, FM_DISCONNECT);
        if (IPS_GetKernelRunlevel() != KR_READY) {
            return;
        }
        $this->RegisterParent();
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
                'type'     => 'connect',
                'moduleIDs'=> [
                    \Plugwise\GUID::Network
                ]
            ]
        );
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
        if (!$this->HasActiveParent()) {
            $Form['actions'][] = [
                'type'  => 'PopupAlert',
                'popup' => [
                    'items' => [[
                        'type'    => 'Label',
                        'caption' => $this->Translate('Instance has no active parent.')
                    ]]
                ]
            ];
            return json_encode($Form);
        }
        $FoundNodes = $this->GetNodes();
        $this->SendDebug('Found', $FoundNodes, 0);
        if (!is_array($FoundNodes)) {
            $FoundNodes = [];
        }
        $InstanceIDListe = $this->GetInstanceList(\Plugwise\GUID::Node);
        $Values = [];
        foreach ($FoundNodes as $NodeMAC) {
            $InstanceID = array_search($NodeMAC, $InstanceIDListe);
            if ($InstanceID !== false) {
                $AddValue = [
                    'instanceID' => $InstanceID,
                    'nodeMAC'    => $NodeMAC,
                    'name'       => IPS_GetName($InstanceID),
                    'location'   => stristr(IPS_GetLocation($InstanceID), IPS_GetName($InstanceID), true)
                ];
                unset($InstanceIDListe[$InstanceID]);
            } else {
                $AddValue = [
                    'instanceID' => 0,
                    'nodeMAC'    => $NodeMAC,
                    'name'       => $this->Translate('Plugwise Device - ') . $NodeMAC,
                    'location'   => ''
                ];
            }
            $AddValue['create'] = [
                'moduleID'      => \Plugwise\GUID::Node,
                'configuration' => [
                    \Plugwise\Node\Property::NodeMAC => $NodeMAC
                ]
            ];
            $Values[] = $AddValue;
        }

        foreach ($InstanceIDListe as $InstanceID => $NodeMAC) {
            $Values[] = [
                'instanceID' => $InstanceID,
                'nodeMAC'    => $NodeMAC,
                'name'       => IPS_GetName($InstanceID),
                'location'   => stristr(IPS_GetLocation($InstanceID), IPS_GetName($InstanceID), true)
            ];
        }
        $this->SetFormProperty($Form, 'Nodes', 'values', $Values);
        return json_encode($Form);
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
     * RequestAction
     *
     * Nimmt nur die internen Meldungen des Parent (IOChangeState) entgegen.
     *
     * @param string $Ident Der Ident der Aktion.
     * @param mixed $Value Der Wert.
     */
    public function RequestAction(string $Ident, mixed $Value): void
    {
        $this->IORequestAction($Ident, $Value);
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
        if ($State == IS_ACTIVE) {
            if ($this->HasActiveParent()) {
                $this->ReloadForm();
            }
        }
    }

    /**
     * SendFunction
     *
     * @param  string $Function
     * @return bool|string|array
     */
    protected function SendFunction(string $Function): bool|string|array
    {
        $this->SendDebug('Send Function', $Function, 0);
        $JSONData = json_encode(
            [
                'DataID'   => '{53FBE996-B1E9-45C2-B8DB-5BD6E5E3F94C}',
                'Function' => bin2hex($Function)]
        );
        $this->SendDebug('Send', $JSONData, 0);
        $ResultString = $this->SendDataToParent($JSONData);
        $this->SendDebug('ResultString', $ResultString, 0);
        if ($ResultString == false) {
            $this->SendDebug('Result Function', 'Error receive data', 0);
            return false;
        }
        /** @var string|array  $Result */
        $Result = @unserialize($ResultString, ['allowed_classes' => false]);
        $this->SendDebug('Result Function', $Result, 0);
        if (($Result === null) || ($Result === false)) {
            $this->SendDebug('Result Function', 'Error receive data', 0);
            return false;
        }
        return $Result;
    }

    /**
     * GetInstanceList
     *
     * @param  string $GUID
     * @return array
     */
    private function GetInstanceList(string $GUID): array
    {
        $InstanceIDList = array_flip(array_values(array_filter(IPS_GetInstanceListByModuleID($GUID), [$this, 'FilterInstances'])));
        array_walk($InstanceIDList, [$this, 'GetConfigParam'], \Plugwise\Node\Property::NodeMAC);
        return $InstanceIDList;
    }

    /**
     * FilterInstances
     *
     * @param  int $InstanceID
     * @return bool
     */
    private function FilterInstances(int $InstanceID): bool
    {
        return IPS_GetInstance($InstanceID)['ConnectionID'] == IPS_GetInstance($this->InstanceID)['ConnectionID'];
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
     * GetNodes
     *
     * Alle bekannten Circles Auslesen
     *
     * @return array|bool
     */
    private function GetNodes(): array|bool
    {
        $CirclePlus = @$this->SendFunction('GetCirclePlusMAC');
        if ($CirclePlus === false) {
            return false;
        }
        $Nodes = @$this->SendFunction('ListNodes');
        if ($Nodes === false) {
            return false;
        }
        return array_merge([0 => $CirclePlus], $Nodes);
    }
}
