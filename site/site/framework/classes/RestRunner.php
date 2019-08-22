<?php

class RestRunner
{
    private $curl = NULL;

    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->curl, CURLOPT_HEADER, false);
}

    public function __destruct()
    {
        curl_close($this->curl);
    }

    public function SetHeader($key, $value)
    {
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array($key.": ".$value));
    }

    public function Post($url, $parms=null)
    {
        $p = "";
        if (is_array($parms))
            $p = $this->BuildParms($parms);
        else
            $p = $parms;

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $p);
        $output = json_decode(curl_exec($this->curl));

        return $output;
    }

    public function Delete($url, $parms=null)
    {
        $p = "";
        if (is_array($parms))
            $p = $this->BuildParms($parms);
        else
            $p = $parms;

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $p);
        
        $output = json_decode(curl_exec($this->curl));

        return $output;
    }

    public function Put($url, $parms=null)
    {
        $p = "";
        if (is_array($parms))
            $p = $this->BuildParms($parms);
        else
            $p = $parms;

        curl_setopt($this->curl, CURLOPT_URL, $url);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $p);

        return $this->Run();
    }

    public function Get($url, $parms=null)
    {
        $p = $this->BuildParms($parms);

        if ($p != "")
            $p = "?".$p;

        curl_setopt($this->curl, CURLOPT_URL, $url.$p);
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);

        return $this->Run();
    }

    private function Run()
    {
        $pre = curl_exec($this->curl);
        $status = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        
        // TODO: Do something with the return code

        $output = json_decode($pre);

        return $output;
    }

    private function BuildParms($parms)
    {
        $p = "";

        if (!$parms)
            return "";

        foreach ($parms as $x)
        {
            $obj = (object) $x;
            $p .= "&".$obj->Key."=".$obj->Value;
        }

        if (substr($p, 0, 1) == "&")
            return substr($p, 1);
        else
            return "";
    }
}




?>
