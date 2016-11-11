<?

require_once(__DIR__ . "/../Plugwise.php");  // diverse Klassen

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
 * PlugwiseCircle Klasse für die Circle des Plugwise-Netzwerk.
 * Erweitert IPSModule.
 *
 * @package       Plugwise
 * @author        Michael Tröger <micha@nall-chan.net>
 * @copyright     2016 Michael Tröger
 * @license       https://creativecommons.org/licenses/by-nc-sa/4.0/ CC BY-NC-SA 4.0
 * @version       0.1
 * @example <b>Ohne</b>
 */
class PlugwiseCircle extends KodiBase
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
        $this->ConnectParent("{7C20491F-F145-4F1C-A69C-AAE1F60F5BD5}");
        $this->RegisterPropertyString("CircleID", "");
        $this->RegisterPropertyBoolean("showCalib", true);
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

            case DM_CONNECT:
            case DM_DISCONNECT:
                $this->ForceRefresh();
                break;
        }
    }

    /**
     * Wird ausgeführt wenn sich der Parent ändert.
     */
    protected function ForceRefresh()
    {
        try
        {
            $this->ApplyChanges();
        }
        catch (Exception $exc)
        {
            unset($exc);
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

        $this->RegisterMessage($this->InstanceID, DM_CONNECT);
        $this->RegisterMessage($this->InstanceID, DM_DISCONNECT);

        if ($this->ReadPropertyBoolean('showCalib'))
        {
            $this->RegisterVariableFloat("gaina", "gaina", "", 0);
            $this->RegisterVariableFloat("gainb", "gainb", "", 0);
            $this->RegisterVariableFloat("offTotal", "offTotal", "", 0);
            $this->RegisterVariableFloat("offNoise", "offNoise", "", 0);
        }
        else
        {
            $this->UnregisterVariable("gaina");
            $this->UnregisterVariable("gainb");
            $this->UnregisterVariable("offTotal");
            $this->UnregisterVariable("offNoise");
        }

        $this->RegisterVariableBoolean("STATE", "STATE", "~Switch", 0);
        $this->EnableAction("STATE");

        //TODO Filter für CircleID
//        $this->SetReceiveDataFilter('.*"Namespace":"' . static::$Namespace . '".*');
//        $this->SendDebug("SetFilter", '.*"Namespace":"' . static::$Namespace . '".*', 0);

        // Wenn Kernel nicht bereit, dann warten... KR_READY über Splitter kommt ja gleich
        if (IPS_GetKernelRunlevel() <> KR_READY)
            return;

        if ($this->HasActiveParent())
        {
            $this->RequestCalibration();
            $this->RequestEnergy();
            $this->RequestState();
        }
    }

################## PRIVATE     

    /**
     * Dekodiert die empfangenen Events und Anworten.
     *
     * @param string $Method RPC-Funktion ohne Namespace
     * @param object $KodiPayload Der zu dekodierende Datensatz als Objekt.
     */
    /*    protected function Decode($Method, $KodiPayload)
      {
      foreach ($KodiPayload as $param => $value)
      {
      switch ($param)
      {
      case "mute":
      case "muted":
      $this->SetValueBoolean("mute", $value);
      break;
      case "volume":
      $this->SetValueInteger("volume", $value);
      break;
      case "name":
      $this->SetValueString("name", $value);
      break;
      case "version":
      $this->SetValueString("version", $value->major . '.' . $value->minor);
      break;
      }
      }
      } */

################## ActionHandler

    /**
     * Actionhandler der Statusvariablen. Interne SDK-Funktion.
     * 
     * @access public
     * @param string $Ident Der Ident der Statusvariable.
     * @param bool|float|int|string $Value Der angeforderte neue Wert.
     */
    public function RequestAction($Ident, $Value)
    {
        switch ($Ident)
        {
            case "STATE":
                return $this->SwitchMode($Value);
            default:
                trigger_error('Invalid Ident.', E_USER_NOTICE);
        }
    }

################## PUBLIC

    /**
     * IPS-Instanz-Funktion 'PLUGWISE_SwitchMode'. Schaltet den Circle ein oder aus.
     *
     * @access public
     * @param bool $Value True für Ein,  False für aus.
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function SwitchMode(bool $Value)
    {
        if (!is_bool($Value))
        {
            trigger_error('Value must be boolean', E_USER_NOTICE);
            return false;
        }
        $PlugwiseData = new Plugwise_Data();
        $PlugwiseData->SetState();
        $ret = $this->Send($PlugwiseData);
        if (is_null($ret))
            return false;
        $this->SetValueBoolean("STATE", $ret);
        if ($ret === $Value)
            return true;
        trigger_error('Error on SwitchMode.', E_USER_NOTICE);
        return false;
    }

    /**
     * IPS-Instanz-Funktion 'PLUGWISE_RequestState'.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestState()
    {
        $PlugwiseData = new Plugwise_Data();
        $PlugwiseData->GetState();
        $ret = $this->Send($PlugwiseData);
        if (is_null($ret))
            return false;
        $this->SetValueBoolean("STATE", $ret);
        return true;
    }

    /**
     * IPS-Instanz-Funktion 'PLUGWISE_RequestCalibration'.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestCalibration()
    {
        $PlugwiseData = new Plugwise_Data();
        $PlugwiseData->GetCalibration();
        $ret = $this->Send($PlugwiseData);
        if (is_null($ret))
            return false;
        // Variablen füllen wenn aktiv.
        // Daten in den Buffer schieben.
        return true;
    }

    /**
     * IPS-Instanz-Funktion 'PLUGWISE_RequestEnergy'.
     *
     * @access public
     * @return bool true bei erfolgreicher Ausführung, sonst false.
     */
    public function RequestEnergy()
    {
        $PlugwiseData = new Plugwise_Data();
        $PlugwiseData->GetEnergy();
        $ret = $this->Send($PlugwiseData);
        if (is_null($ret))
            return false;
        // Calib aus Buffer holen und Werte berechnen.
        // Variablen füllen.
        return true;
    }

    /**
     * Prüft den Parent auf vorhandensein und Status.
     * 
     * @return bool True wenn Parent vorhanden und in Status 102, sonst false.
     */
    protected function HasActiveParent()
    {
        $instance = IPS_GetInstance($this->InstanceID);
        if ($instance['ConnectionID'] > 0)
        {
            $parent = IPS_GetInstance($instance['ConnectionID']);
            if ($parent['InstanceStatus'] == 102)
                return true;
        }
        return false;
    }

}

/** @} */
?>