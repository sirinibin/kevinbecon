<?php

class Kevin
{
    private $files = [];

    public $filesDir = "films";

    private $films = [];

    private $actor;//Actor info;

    private $degree;

    public $path = [];

    public $kevin = [
        'name' => 'Kevin Bacon',
        'image' => 'http://image.tmdb.org/t/p/w185/bMVujB1SaHhXD5gQdO4Xf47WXD3.jpg'
    ];

    public $found = false;

    public $degreeLimit = 6; //Upto which degree we need to search

    private $errorMessage;

    public function __construct()
    {
        $this->degree = 1;

        $this->setFiles();

    }

    //run
    public function run($actorName)
    {

        $this->actor = $this->setActor($actorName);
        if (!$this->actor) {
            $this->errorMessage = "Invalid Actor entered";
            $this->printError();
            return false;
        }
        if ($actorName == "Kevin Bacon") {
            $this->errorMessage = "Please enter a different actor name other than 'Kevin Bacon'";
            $this->printError();
            return false;
        }

        $result = $this->getActorConnections($this->actor);


        if ($result['kevin']) {

            $this->path[] = $result['kevin'];
            $this->found = true;
            // return true;

        } else {

            $nextDegreeConnections = $result['connections'];
            while (!$this->found) {
                $nextDegreeConnections = $this->findNextDegreeConnections($nextDegreeConnections);

                if ($this->degree > $this->degreeLimit) {
                    $this->found = false;
                    $this->errorMessage = "<br/>Not found \"Kevin Bacon\" even in " . $this->degreeLimit . " Degree connections of " . $actorName;
                    break;
                }
            }
        }

        if (!$this->found) {

            $this->printError();

        } else {

            $this->printOutput();
        }

        $this->printProcessingTime();
    }

    //set the files
    public function setFiles()
    {

        $this->files = scandir($this->filesDir);

        foreach ($this->files as $f) {
            if (substr_count($f, ".json") == 0)
                continue;

            $content = file_get_contents("films/" . $f);
            $content = json_decode($content);
            $content = $this->object_to_array($content);

            $item = [
                'name' => $content['film']['name'],
                'filename' => $f
            ];

            if (isset($content['film']['image'])) {
                $item['image'] = $content['film']['image'];
            }


            $item['cast'] = $content['cast'];

            $this->films[] = $item;
        }

        return true;
    }

    //get the list of all files
    public function getFiles()
    {

        return $this->files;
    }

    //get the list of all films
    public function getFilms()
    {

        return $this->films;
    }

    //get current actor info
    public function getActor()
    {

        return $this->actor;
    }

    //get current connection Degree
    public function getDegree()
    {

        return $this->degree;
    }

    //sets an actor Info
    private function setActor($name)
    {
        foreach ($this->films as $f) {

            foreach ($f['cast'] as $c) {

                if ($c['name'] == $name) {

                    return $c;
                }
            }

        }
        return false;

    }

    /* To get all the connections of an actor*/
    function getActorConnections($actor)
    {

        $user_connections = [];

        $kevin_object = false;

        foreach ($this->films as $k1 => $f) {

            $found = false;
            foreach ($f['cast'] as $k2 => $c) {

                $f['cast'][$k2]['film'] = $f['name'];
                $f['cast'][$k2]['film_image'] = isset($f['image']) ? $f['image'] : '';
                $f['cast'][$k2]['filename'] = $f['filename'];
                $f['cast'][$k2]['connected_from'] = $actor;

                if ($c['name'] == $actor['name']) {

                    $found = true;
                    unset($f['cast'][$k2]);

                }
            }

            if ($found) {

                $user_connections = array_merge($user_connections, $f['cast']);
                $kevin_object = $this->isKevinConnected($user_connections);
            }

            if ($kevin_object) {
                break;
            }

        }

        $user_connections = array_unique($user_connections, SORT_REGULAR);

        return ['connections' => $user_connections, 'kevin' => $kevin_object];
    }

    //To get check if there is kevin becon is in a list of connections
    public function isKevinConnected($connections)
    {

        foreach ($connections as $c) {
            if ($c['name'] == $this->kevin['name']) {
                return $c;
            }
        }
        return false;
    }

    //to find next Degree connections
    function findNextDegreeConnections($connections)
    {

        $this->degree++;
        $nextDegreeConnections = [];
        foreach ($connections as $c1) {

            $result = $this->getActorConnections($c1);

            if ($result['kevin']) {

                $this->path[] = $result['kevin'];
                $this->found = true;
                return true;

            } else {
                $nextDegreeConnections = array_merge($nextDegreeConnections, $result['connections']);
                continue;

            }
        }

        $nextDegreeConnections = array_unique($nextDegreeConnections, SORT_REGULAR);

        return $nextDegreeConnections;
    }

    //print processing time
    public function printProcessingTime()
    {
        $time = microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"];
        echo "<br/>Time: {$time}";
    }

    //convert an object to array
    private function object_to_array($obj)
    {
        if (is_object($obj)) $obj = (array)$obj;
        if (is_array($obj)) {
            $new = array();
            foreach ($obj as $key => $val) {
                $new[$key] = $this->object_to_array($val);
            }
        } else $new = $obj;
        return $new;
    }

    //print error
    public function printError()
    {
        echo "<br/>" . $this->errorMessage;
    }

    //print output
    public function printOutput()
    {

        echo "<pre><br/>Degree:" . $this->getDegree() . "<br/>";
        //print_r($this->path);


        if (is_array($this->path) && count($this->path) > 0) {
            echo "<table border='1' >";

            echo "<tr rowspan='2' >";

            $this->printColumn($this->path[0]);
            echo "</tr>";

            echo "</table>";
        }


    }

    private function  printColumn($path)
    {
        if (!isset($path['name']))
            return;

        echo "<td width='100'  >";

        if(isset($path['image'])){

            echo "<img src='" . $path['image'] . "'><br/>";
        }

        echo  $path['name'];

        echo "</td>";


        if (isset($path['film'])) {

            echo "<td><b>==></b></td>";

            echo "<td width='100' >";
            if (isset($path['film_image'])) {


                echo "<img src='" . $path['film_image'] . "'>";
            }

            echo "<br/>" . $path['film'] . "[file:" . $path['filename'] . "]";
        }
        echo "</td>";


        if (isset($path['connected_from'])) {

            echo "<td><b>==></b></td>";
            $this->printColumn($path['connected_from']);
        }


    }

}
