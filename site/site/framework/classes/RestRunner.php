<?php

class RestRunner
{
    private $curl = NULL;

    public function __construct()
    {
        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct()
    {
        curl_close($curl);
    }

    public function Post($url, $parms)
    {
        $p = $this->BuildParms($parms);

        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_POST, 1);
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $p);
        $output = json_decode(curl_exec($curl));

        return $output;
    }

    public function Put($url, $parms)
    {
        $p = $this->BuildParms($parms);

        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt ($curl, CURLOPT_POSTFIELDS, $p);

        return $this->Run();
    }

    public function Get($url, $parms)
    {
        $p = $this->BuildParms($parms);

        curl_setopt ($curl, CURLOPT_URL, $url.$p);

        return $this->Run();
    }

    private function Run()
    {
        $pre = curl_exec($curl);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // TODO: Do something with the return code

        $output = json_decode($pre);

        return $output;
    }

    private function BuildParms($parms)
    {
        $p = "";

        foreach ($parms as $x)
        {
            $p += "&".$x->Key."=".$x->Value;
        }

        if (substr($p, 0, 1) == "&")
            return "?".substr($p, 1);
        else
            return "";
    }
}




?>
