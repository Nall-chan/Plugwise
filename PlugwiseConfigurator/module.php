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

/**
 * PlugwiseConfigurator Klasse für den Konfigurator eines Plugwise-Netzwerk.
 * Erweitert IPSModule.
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2026 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       2.00
 * @property int $ParentID Die InstanzeID des IO-Parent
 * @method int RegisterParent()
 * @method void RegisterProfileFloat(string $Name, string $Icon, string $Prefix, string $Suffix, float $MinValue, float $MaxValue, float $StepSize, int $Digits)
 * @method bool IORequestAction(string $Ident, mixed $Value)
 * @method void IOMessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data)
 */
class PlugwiseConfigurator extends IPSModuleStrict
{
    use \Plugwise\BufferHelper;
    use \Plugwise\DebugHelper;
    use \Plugwise\InstanceStatus{
        \Plugwise\InstanceStatus::MessageSink as IOMessageSink;
        \Plugwise\InstanceStatus::RequestAction as IORequestAction;
    }

    /**
     * ApplyChanges
     *
     * @return void
     */
    public function ApplyChanges(): void
    {
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
                        'caption' => 'Instance has no active parent.'
                    ]]
                ]
            ];
            $this->SendDebug('FORM', json_encode($Form), 0);
            $this->SendDebug('FORM', json_last_error_msg(), 0);
            return json_encode($Form);

        }
        $FoundNodes = $this->GetNodes();
        $this->SendDebug('Found', $FoundNodes, 0);
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
        $Form['actions'][0]['values'] = $Values;
        $this->SendDebug('FORM', json_encode($Form), 0);
        $this->SendDebug('FORM', json_last_error_msg(), 0);
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
        $Result = @unserialize($ResultString);
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
     * Send
     *
     * Konvertiert $PlugwiseData zu einem JSONString und versendet diese an den Splitter.
     *
     * @param \Plugwise\Plugwise_Frame $PlugwiseData Zu versendende Daten.
     * @return \Plugwise\Plugwise_Frame|bool Objekt mit der Antwort. false im Fehlerfall.
     */
    private function Send(\Plugwise\Plugwise_Frame $PlugwiseData): bool|\Plugwise\Plugwise_Frame
    {
        $this->SendDebug('Send', $PlugwiseData, 0);
        $JSONData = $PlugwiseData->ToJSONStringForSplitter();
        $ResultString = @$this->SendDataToParent($JSONData);
        $this->SendDebug('Send', $JSONData, 0);
        if ($ResultString == false) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            return false;
        }
        /** @var \Plugwise\Plugwise_Frame|bool $Result */
        $Result = @unserialize($ResultString);
        $this->SendDebug('Response', $Result, 0);

        if (($Result === null) || ($Result === false)) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            return false;
        }
        return $Result;
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
