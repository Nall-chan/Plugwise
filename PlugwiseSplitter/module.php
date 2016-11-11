<?

require_once(__DIR__ . "/../Plugwise.php");  // diverse Klassen

/*
 * @addtogroup plugwise
 * @{
 *
 * @package       Plugwise
 * @file          module.php
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       0.1
 *
 */

/**
 * PlugwiseSplitter Klasse für die Kommunikation mit der RF-Stick für das Plugwise-Netzwerk.
 * Enthält den Namespace PLUGWISE.
 * Erweitert IPSModule.
 * 
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       0.1 
 * @example <b>Ohne</b>
 */
class PlugwiseSplitter extends IPSModule
{

    use Plugwise;

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function Create()
    {
        parent::Create();
        $this->RequireParent("{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}");
    }

    /**
     * Interne Funktion des SDK.
     *
     * @access public
     */
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        switch ($Message)
        {
            case IPS_KERNELMESSAGE:
                if ($Data[0] == KR_READY)
                    $this->KernelReady();

                break;
            case DM_CONNECT:
            case DM_DISCONNECT:
                $this->ForceRefresh();
                break;
            case IM_CHANGESTATUS:
                if (($SenderID == @IPS_GetInstance($this->InstanceID)['ConnectionID']) and ( $Data[0] == IS_ACTIVE))
                    $this->ForceRefresh();
                break;
        }
    }

    /**
     * Wird ausgeführt wenn der Kernel hochgefahren wurde.
     */
    protected function KernelReady()
    {
        if ($this->SetParentConfig())
            try
            {
                $this->InitStick();
            }
            catch (Exception $exc)
            {
                trigger_error($exc->getMessage(), $exc->getCode());
            }
    }

    /**
     * Wird ausgeführt wenn sich der Parent ändert.
     */
    protected function ForceRefresh()
    {
        if ($this->SetParentConfig())
            try
            {
                $this->InitStick();
            }
            catch (Exception $exc)
            {
                trigger_error($exc->getMessage(), $exc->getCode());
            }
    }

    /**
     * Interne Funktion des SDK.
     * 
     * @access public
     */
    public function ApplyChanges()
    {
        parent::ApplyChanges();

        $this->SetReceiveDataFilter(".*018EF6B5-AB94-40C6-AA53-46943E824ACF.*");
        $this->RegisterMessage(0, IPS_KERNELMESSAGE);
        $this->RegisterMessage($this->InstanceID, DM_CONNECT);
        $this->RegisterMessage($this->InstanceID, DM_DISCONNECT);

        // Wenn Kernel nicht bereit, dann warten... KR_READY kommt ja gleich
        if (IPS_GetKernelRunlevel() <> KR_READY)
            return;

        // Buffer leeren
        $this->SetBuffer('ReplyJSONData', serialize(array())); // 


        if ($this->SetParentConfig())
            try
            {
                $this->InitStick();
            }
            catch (Exception $exc)
            {
                trigger_error($exc->getMessage(), $exc->getCode());
            }
    }

################## PRIVATE     

    /**
     * Konfiguriert den Parent.
     * 
     * @access private
     */
    private function SetParentConfig()
    {
        $OldParentId = $this->GetBuffer('Parent');
        $ParentId = @IPS_GetInstance($this->InstanceID)['ConnectionID'];
        if ($ParentId <> $OldParentId)
        {
            if ($OldParentId > 0)
                $this->UnregisterMessage($OldParentId, IM_CHANGESTATUS);
            if ($ParentId > 0)
            {
                $this->RegisterMessage($ParentId, IM_CHANGESTATUS);
                $this->SetBuffer('Parent', $ParentId);
            }
        }

        if ($ParentId > 0)
        {
            $this->SetSummary(IPS_GetProperty($ParentId, 'Port'));

            $ParentInstance = IPS_GetInstance($ParentId);
            if ($ParentInstance['ModuleInfo']['ModuleID'] == '{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}')
            {
                if (IPS_GetProperty($ParentId, 'StopBits') <> '1')
                    IPS_SetProperty($ParentId, 'StopBits', '1');
                if (IPS_GetProperty($ParentId, 'BaudRate') <> '115200')
                    IPS_SetProperty($ParentId, 'BaudRate', '115200');
                if (IPS_GetProperty($ParentId, 'Parity') <> 'None')
                    IPS_SetProperty($ParentId, 'Parity', 'None');
                if (IPS_GetProperty($ParentId, 'DataBits') <> '8')
                    IPS_SetProperty($ParentId, 'DataBits', '8');
                if (IPS_HasChanges($ParentId))
                    IPS_ApplyChanges($ParentId);
            }
            $this->SetStatus(IS_ACTIVE);
        }
        else
        {
            $this->SetSummary('(none)');
            $this->SetStatus(IS_INACTIVE);
        }
    }

    /**
     * Dekodiert die empfangenen Pakete und Anworten auf.
     * 
     * @access protected
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    protected function Decode($Method, $Event)
    {
        $this->SendDebug('KODI_Event', $Event, 0);
    }

################## PUBLIC
################## DATAPOINTS DEVICE

    /**
     * Interne Funktion des SDK. Nimmt Daten von Childs entgegen und sendet Diese weiter.
     * 
     * @access public
     * @param string $JSONString Ein Kodi_RPC_Data-Objekt welches als JSONString kodiert ist.
     * @result bool true wenn Daten gesendet werden konnten, sonst false.
     */
    public function ForwardData($JSONString)
    {
//        $this->SendDebug('Forward', $JSONString, 0);

        $Data = json_decode($JSONString);
//        if ($Data->DataID <> "{0222A902-A6FA-4E94-94D3-D54AA4666321}")
//            return false;
        $KodiData = new Kodi_RPC_Data();
        $KodiData->CreateFromGenericObject($Data);
//        try
//        {
        $ret = $this->Send($KodiData);
        //          $this->SendDebug('Result', $anwser, 0);
        if (!is_null($ret))
            return serialize($ret);
//        }
//        catch (Exception $ex)
//        {
//            trigger_error($ex->getMessage(), $ex->getCode());
//        }
        return false;
    }

    /**
     * Sendet Kodi_RPC_Data an die Childs.
     * 
     * @access private
     * @param Kodi_RPC_Data $KodiData Ein Kodi_RPC_Data-Objekt.
     */
    private function SendDataToDevice(Kodi_RPC_Data $KodiData)
    {
        $Data = $KodiData->ToJSONString('{73249F91-710A-4D24-B1F1-A72F216C2BDC}');
        $this->SendDebug('IPS_SendDataToChildren', $Data, 0);
        $this->SendDataToChildren($Data);
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

        // Datenstream zusammenfügen
        $head = $this->GetBuffer('BufferIN');
//        $this->SetBuffer('BufferIN', '');
        $Data = $head . utf8_decode($data->Buffer);

        // Stream in einzelne Pakete schneiden
        $Data = str_replace('}{', '}' . chr(0x04) . '{', $Data, $Count);
        $JSONLine = explode(chr(0x04), $Data);

        if (is_null(json_decode($JSONLine[$Count])))
        {
            // Rest vom Stream wieder in den Empfangsbuffer schieben
            $tail = array_pop($JSONLine);
            $this->SetBuffer('BufferIN', $tail);
        }
        else
            $this->SetBuffer('BufferIN', '');

        // Pakete verarbeiten
        foreach ($JSONLine as $JSON)
        {
            $KodiData = new Kodi_RPC_Data();
            $KodiData->CreateFromJSONString($JSON);
            if ($KodiData->Typ == Kodi_RPC_Data::$ResultTyp) // Reply
            {
                try
                {
                    $this->SendQueueUpdate($KodiData->Id, $KodiData);
                }
                catch (Exception $exc)
                {
                    $buffer = $this->GetBuffer('BufferIN');
                    $this->SetBuffer('BufferIN', $JSON . $buffer);
                    trigger_error($exc->getMessage(), E_USER_NOTICE);
                    continue;
                }
            }
            else if ($KodiData->Typ == Kodi_RPC_Data::$EventTyp) // Event
            {
                $this->SendDebug('KODI_Event', $KodiData, 0);
                $this->SendDataToDevice($KodiData);
                if (self::$Namespace == $KodiData->Namespace)
                    $this->Decode($KodiData->Method, $KodiData->GetEvent());
            }
        }
        return true;
    }

    /**
     * Versendet ein Kodi_RPC-Objekt und empfängt die Antwort.
     * 
     * @access protected
     * @param Kodi_RPC_Data $KodiData Das Objekt welches versendet werden soll.
     * @result mixed Enthält die Antwort auf das Versendete Objekt oder NULL im Fehlerfall.
     */
    protected function Send(Kodi_RPC_Data $KodiData)
    {
        try
        {
            if ($this->ReadPropertyBoolean('Open') === false)
                throw new Exception('Instance inactiv.', E_USER_NOTICE);

            if (!$this->HasActiveParent())
                throw new Exception('Intance has no active parent.', E_USER_NOTICE);
            $this->SendDebug('Send', $KodiData, 0);
            $this->SendQueuePush($KodiData->Id);
            $this->SendDataToParent($KodiData);
            $ReplyKodiData = $this->WaitForResponse($KodiData->Id);

            if ($ReplyKodiData === false)
            {
                //$this->SetStatus(IS_EBASE + 3);
                throw new Exception('No anwser from Kodi', E_USER_NOTICE);
            }

            $ret = $ReplyKodiData->GetResult();
            if (is_a($ret, 'KodiRPCException'))
            {
                throw $ret;
            }
            $this->SendDebug('Receive', $ReplyKodiData, 0);
            return $ret;
        }
        catch (KodiRPCException $ex)
        {
            $this->SendDebug("Receive", $ex, 0);
            trigger_error('Error (' . $ex->getCode() . '): ' . $ex->getMessage(), E_USER_NOTICE);
        }
        catch (Exception $ex)
        {
            $this->SendDebug("Receive", $ex->getMessage(), 0);
            trigger_error($ex->getMessage(), $ex->getCode());
        }
        return NULL;
    }

    /**
     * Sendet ein Kodi_RPC-Objekt an den Parent.
     * 
     * @access protected
     * @param Kodi_RPC_Data $Data Das Objekt welches versendet werden soll.
     * @result bool true
     */
    protected function SendDataToParent($Data)
    {
        $JsonString = $Data->ToRPCJSONString('{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}');
        parent::SendDataToParent($JsonString);
        return true;
    }

    /**
     * Wartet auf eine RPC-Antwort.
     * 
     * @access private
     * @param int $Id Die RPC-ID auf die gewartet wird.
     * @result mixed Enthält ein Kodi_RPC_Data-Objekt mit der Antwort, oder false bei einem Timeout.
     */
    private function WaitForResponse($Id)
    {
        for ($i = 0; $i < 1000; $i++)
        {
            if ($this->GetBuffer('ReplyJSONData') === 'a:0:{}') // wenn wenig los, gleich warten            
                IPS_Sleep(5);
            else
            {
                $ret = unserialize($this->GetBuffer('ReplyJSONData'));
                if (!array_key_exists(intval($Id), $ret))
                    return false;
                if ($ret[$Id] <> "")
                    return $this->SendQueuePop($Id);
                IPS_Sleep(5);
            }
        }
        $this->SendQueueRemove($Id);
        return false;
    }

################## SENDQUEUE

    /**
     * Fügt eine Anfrage in die SendQueue ein.
     * 
     * @access private
     * @param int $Id die RPC-ID des versendeten RPC-Objektes.
     */
    private function SendQueuePush(int $Id)
    {
        if (!$this->lock('ReplyJSONData'))
            throw new Exception('ReplyJSONData is locked', E_USER_NOTICE);
        $data = unserialize($this->GetBuffer('ReplyJSONData'));
        $data[$Id] = "";
        $this->SetBuffer('ReplyJSONData', serialize($data));
        $this->unlock('ReplyJSONData');
    }

    /**
     * Fügt eine RPC-Antwort in die SendQueue ein.
     * 
     * @access private
     * @param int $Id die RPC-ID des empfangenen Objektes.
     * @param Kodi_RPC_Data $KodiData Das empfangene RPC-Result.
     */
    private function SendQueueUpdate(int $Id, Kodi_RPC_Data $KodiData)
    {
        if (!$this->lock('ReplyJSONData'))
            throw new Exception('ReplyJSONData is locked', E_USER_NOTICE);
        $data = unserialize($this->GetBuffer('ReplyJSONData'));
        $data[$Id] = $KodiData->ToJSONString("");
        $this->SetBuffer('ReplyJSONData', serialize($data));
        $this->unlock('ReplyJSONData');
    }

    /**
     * Holt eine RPC-Antwort aus der SendQueue.
     * 
     * @access private
     * @param int $Id die RPC-ID des empfangenen Objektes.
     * @return Kodi_RPC_Data Das empfangene RPC-Result.
     */
    private function SendQueuePop(int $Id)
    {
        $data = unserialize($this->GetBuffer('ReplyJSONData'));
        $Result = new Kodi_RPC_Data();
        $JSONObject = json_decode($data[$Id]);
        $Result->CreateFromGenericObject($JSONObject);
        $this->SendQueueRemove($Id);
        return $Result;
    }

    /**
     * Löscht einen RPC-Eintrag aus der SendQueue.
     * 
     * @access private
     * @param int $Id Die RPC-ID des zu löschenden Objektes.
     */
    private function SendQueueRemove(int $Id)
    {
        if (!$this->lock('ReplyJSONData'))
            throw new Exception('ReplyJSONData is locked', E_USER_NOTICE);
        $data = unserialize($this->GetBuffer('ReplyJSONData'));
        unset($data[$Id]);
        $this->SetBuffer('ReplyJSONData', serialize($data));
        $this->unlock('ReplyJSONData');
    }

################## DUMMYS / WORKAROUNDS - protected

    /**
     * Formatiert eine DebugAusgabe und gibt sie an IPS weiter.
     *
     * @access protected
     * @param string $Message Nachrichten-Feld.
     * @param string|array|Kodi_RPC_Data $Data Daten-Feld.
     * @param int $Format Ausgabe in Klartext(0) oder Hex(1)
     */
    protected function SendDebug($Message, $Data, $Format)
    {
        if (is_a($Data, 'Kodi_RPC_Data'))
        {
            switch ($Data->Typ)
            {
                case Kodi_RPC_Data::$EventTyp:
                    $this->SendDebug($Message . " Event", $Data->GetEvent(), 0);
                    break;
                case Kodi_RPC_Data::$ResultTyp:
                    $this->SendDebug($Message . " Result", $Data->GetResult(), 0);
                    break;
                default:
                    parent::SendDebug($Message . " Method", $Data->Namespace . '.' . $Data->Method, 0);
                    $this->SendDebug($Message . " Params", $Data->Params, 0);
                    break;
            }
        }
        else if (is_a($Data, 'KodiRPCException'))
        {
            parent::SendDebug('Error', $Data->getCode() . ' : ' . $Data->getMessage(), 0);
        }
        elseif (is_array($Data))
        {
            foreach ($Data as $Key => $DebugData)
            {
                $this->SendDebug($Message . ":" . $Key, $DebugData, 0);
            }
        }
        else if (is_object($Data))
        {
            foreach ($Data as $Key => $DebugData)
            {
                $this->SendDebug($Message . ":" . $Key, $DebugData, 0);
            }
        }
        else
        {
            parent::SendDebug($Message, $Data, $Format);
        }
    }

    /**
     * Liefert den Parent der Instanz.
     * 
     * @return int|bool InstanzID des Parent, false wenn kein Parent vorhanden.
     */
    protected function GetParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        return ($instance['ConnectionID'] > 0) ? $instance['ConnectionID'] : false;
    }

    /**
     * Prüft den Parent auf vorhandensein und Status.
     * 
     * @return bool True wenn Parent vorhanden und in Status 102, sonst false.
     */
    protected function HasActiveParent()
    {
        $ParentID = $this->GetParent();
        if ($ParentID !== false)
        {
            if (IPS_GetInstance($ParentID)['InstanceStatus'] == 102)
                return true;
        }
        return false;
    }

    /**
     * Erzeugt einen neuen Parent, wenn keiner vorhanden ist.
     * 
     * @param string $ModuleID Die GUID des benötigten Parent.
     */
    protected function RequireParent($ModuleID)
    {
        $instance = IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] == 0)
        {
            $parentID = IPS_CreateInstance($ModuleID);
            $instance = IPS_GetInstance($parentID);
            IPS_SetName($parentID, "Plugwise RF-Stick");
            IPS_ConnectInstance($this->InstanceID, $parentID);
        }
    }

    /**
     * Setzt den Status dieser Instanz auf den übergebenen Status.
     * Prüft vorher noch ob sich dieser vom aktuellen Status unterscheidet.
     * 
     * @param int $InstanceStatus
     */
    protected function SetStatus($InstanceStatus)
    {
        if ($InstanceStatus <> IPS_GetInstance($this->InstanceID)['InstanceStatus'])
            parent::SetStatus($InstanceStatus);
    }

################## SEMAPHOREN Helper  - private  

    /**
     * Setzt einen 'Lock'.
     *      * 
     * @param string $ident Ident der Semaphore
     * @return bool True bei Erfolg, false bei Misserfolg.
     */
    private function lock(string $ident)
    {
        for ($i = 0; $i < 100; $i++)
        {
            if (IPS_SemaphoreEnter("KODI_" . (string) $this->InstanceID . (string) $ident, 1))
                return true;
            else
                IPS_Sleep(mt_rand(1, 5));
        }
        return false;
    }

    /**
     * Löscht einen 'Lock'.
     * 
     * @param string $ident Ident der Semaphore
     */
    private function unlock(string $ident)
    {
        IPS_SemaphoreLeave("KODI_" . (string) $this->InstanceID . (string) $ident);
    }

}

/** @} */
?>