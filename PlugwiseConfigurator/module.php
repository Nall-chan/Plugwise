<?php

require_once(__DIR__ . "/../libs/Plugwise.php");  // diverse Klassen

/*
 * @addtogroup plugwise
 * @{
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       0.1
 * @example <b>Ohne</b>
 */

/**
 * PlugwiseConfigurator Klasse für den Konfigurator eines Plugwise-Netzwerk.
 * Erweitert IPSModule.
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       0.1
 * @example <b>Ohne</b>
 */
class PlugwiseConfigurator extends IPSModule
{
    use DebugHelper;

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->ConnectParent("{7C20491F-F145-4F1C-A69C-AAE1F60F5BD5}");
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();
    }

    /**
     * Alle bekannten Circles Auslesen
     * FERTIG
     */
    private function GetNodes()
    {
        $CirclePlus = @$this->SendFunction('GetCirclePlusMAC');
        if ($CirclePlus === false) {
            return false;
        }


        $Nodes = @$this->SendFunction('ListNodes');
        if ($Nodes === false) {
            return false;
        }
        return array_merge(array(0 => $CirclePlus), $Nodes);
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function GetConfigurationForm()
    {
        $FoundNodes = $this->GetNodes();
        if ($FoundNodes === false) {
            return;
        }
        $Total = count($FoundNodes);
        $MyParent = IPS_GetInstance($this->InstanceID)['ConnectionID'];
        $Liste = array();
        $DisconnectedNodes = 0;
        $NewNodes = 0;
        $this->SendDebug('Found', $FoundNodes, 0);
        $InstanceIDListe = IPS_GetInstanceListByModuleID("{5FD73328-68F3-4047-B678-E385C2E31962}");
        foreach ($InstanceIDListe as $InstanceID) {
            // Fremde Geräte überspringen
            if (IPS_GetInstance($InstanceID)['ConnectionID'] != $MyParent) {
                continue;
            }
            $NodeMAC = IPS_GetProperty($InstanceID, 'NodeMAC');
            $Node = array(
                'InstanceID' => $InstanceID,
                'NodeMAC' => $NodeMAC,
                'Name' => IPS_GetName($InstanceID),
                'Location' => stristr(IPS_GetLocation($InstanceID), IPS_GetName($InstanceID), true)
            );
            $this->SendDebug('Search', $NodeMAC, 0);
            $FoundIndex = array_search($NodeMAC, $FoundNodes);
            if ($FoundIndex === false) {
                $Node['Index'] = "";
                $Node["rowColor"] = "#ff0000";
                $DisconnectedNodes++;
            } else {
                $Node['Index'] = (string) $FoundIndex;
                $Node['rowColor'] = "#00ff00";
                unset($FoundNodes[$FoundIndex]);
            }

            $Liste[] = $Node;
        }
        foreach ($FoundNodes as $Index => $NodeMAC) {
            $Node = array(
                'Index' => (string) $Index,
                'InstanceID' => 0,
                'NodeMAC' => $NodeMAC,
                'Name' => '',
                'Location' => '');
            $Liste[] = $Node;
            $NewNodes++;
        }


        $data = json_decode(file_get_contents(__DIR__ . "/form.json"), true);
        if ($Total > 0) {
            $data['actions'][2]['label'] = sprintf($this->Translate("Nodes in network: %d"), $Total);
        }
        if ($NewNodes > 0) {
            $data['actions'][4]['label'] = sprintf($this->Translate("New nodes: %d"), $NewNodes);
        }
        if ($DisconnectedNodes > 0) {
            $data['actions'][5]['label'] = sprintf($this->Translate("Deleted nodes : %d"), $DisconnectedNodes);
        }
        $data['actions'][7]['values'] = array_merge($data['actions'][7]['values'], $Liste);


        $data['actions'][9]['onClick'] = <<<'EOT'
if (($Nodes['NodeMAC'] == '') or ($Nodes['InstanceID'] > 0))
    return;
$InstanceID = IPS_CreateInstance('{5FD73328-68F3-4047-B678-E385C2E31962}');
if ($InstanceID == false) return;
if (IPS_GetInstance($InstanceID)['ConnectionID'] != IPS_GetInstance($id)['ConnectionID'])
{
    if (IPS_GetInstance($InstanceID)['ConnectionID'] > 0)
    IPS_DisconnectInstance($InstanceID);
    IPS_ConnectInstance($InstanceID, IPS_GetInstance($id)['ConnectionID']);
}
@IPS_SetProperty($InstanceID, 'NodeMAC', $Nodes['NodeMAC']);
@IPS_SetProperty($InstanceID, 'Interval', $Interval);
@IPS_ApplyChanges($InstanceID);
IPS_SetName($InstanceID,'Plugwise Node - '.$Nodes['NodeMAC']);
echo 'OK';
EOT;
        //$this->SendDebug('FORM', json_encode($data), 0);
        return json_encode($data);
    }

    ################## Datapoints

    /**
     * Konvertiert $Data zu einem JSONString und versendet diese an den Splitter.
     *
     * @access protected
     * @param Plugwise_Frame $PlugwiseData Zu versendende Daten.
     * @return Plugwise_Frame Objekt mit der Antwort. NULL im Fehlerfall.
     */
    protected function Send(Plugwise_Frame $PlugwiseData)
    {
        $this->SendDebug('Send', $PlugwiseData, 0);
        $JSONData = $PlugwiseData->ToJSONStringForSplitter();
        $ResultString = @$this->SendDataToParent($JSONData);
        $this->SendDebug('Send', $JSONData, 0);
        if ($ResultString === false) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            //echo 'Error receive data';
            return false;
        }
        $Result = @unserialize($ResultString);
        $this->SendDebug('Response', $Result, 0);

        if (($Result === null) or ($Result === false)) {
            $this->SendDebug('Receive', 'Error receive data', 0);
            //echo 'Error on send data';
            return false;
        }
        return $Result;
    }

    protected function SendFunction($Function)
    {
        $this->SendDebug('Send Function', $Function, 0);

        $JSONData = json_encode(array(
            "DataID" => '{53FBE996-B1E9-45C2-B8DB-5BD6E5E3F94C}',
            "Function" => utf8_encode($Function))
        );
        $this->SendDebug('Send', $JSONData, 0);
        $ResultString = $this->SendDataToParent($JSONData);
        $this->SendDebug('ResultString', $ResultString, 0);
        if ($ResultString === false) {
            $this->SendDebug('Result Function', 'Error receive data', 0);
            //echo 'Error receive data';
            return false;
        }
        $Result = @unserialize($ResultString);
        $this->SendDebug('Result Function', $Result, 0);

        if (($Result === null) or ($Result === false)) {
            $this->SendDebug('Result Function', 'Error receive data', 0);
            //echo 'Error on send data';
            return false;
        }
        return $Result;
    }
}

/** @} */
