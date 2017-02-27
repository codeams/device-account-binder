<?php
require_once 'LdapHandler.php';
require_once 'SwitchHandler.php';

class CommunicationHandler {
    private $connection;
    private $server = 'localhost';
    private $name = 'usuarios';

    public function receiveRequest( $request ) {

        $response = null;

        switch ($request['method']) {

            case 'addDevice':
                $response['response'] = $this->addDevice($request);
                break;

            case 'deleteDevice':
                $response['response'] = $this->deleteDevice($request);
                break;

            case 'updateDevice':
                $response['response'] = $this->updateDevice($request);
                break;

            case 'getUserDevices':
                $response['response'] = $this->getUserDevices($request);
                break;

            case 'authentication':
                $response['response'] = LdapHandler::validateUser($request['user'], $request['password']);
                break;

        }

        $reportAsJsonEncodedArray = json_encode( $response);

        print ( $reportAsJsonEncodedArray );

    }

    private function getDevice($id){
        $this->connect( 'root','2016rys#.' );
        $query = "SELECT * FROM dispositivos WHERE ( idDispositivo ='$id')";
        $areRowsFetched = mysqli_query(  $this->connection, $query )
        or die(mysqli_error($this->connection));
        if ( $areRowsFetched ) {

            $fetchedRows = $areRowsFetched;
            $selectedRows = array();

            while ($row = mysqli_fetch_assoc($fetchedRows)) {

                array_push($selectedRows, $row);

            }
        }

        $device = $selectedRows[0];
        return $device;

    }

    public function getUserDevices($request){
        $this->connect('root','2016rys#.' );
        $id = $request['id'];
        $query = "SELECT * FROM (dispositivos d JOIN pertenencias p ON d.idDispositivo = p.idDispositivo) WHERE ( matricula ='$id')";
        $areRowsFetched = mysqli_query(  $this->connection, $query )
        or die(mysqli_error($this->connection));
        if ( $areRowsFetched ) {

            $fetchedRows = $areRowsFetched;
            $selectedRows = array();

            while ($row = mysqli_fetch_assoc($fetchedRows)) {

                array_push($selectedRows, $row);

            }
        }
            return $selectedRows;



    }

    public function addDevice($request){

        $this->connect('root','2016rys#.' );
        $mac = $request['mac'];
        $alias = $request['alias'];
        $matricula = $request['matricula'];

        $query = "INSERT INTO dispositivos (nombre, mac) VALUES ('$alias','$mac')";
        mysqli_query(  $this->connection, $query )
        or die(mysqli_error($this->connection));

        $insertedId = mysqli_insert_id($this->connection);
        $deviceAdded = $insertedId;
        $query = "INSERT INTO pertenencias (matricula, idDispositivo) VALUES ('$matricula','$deviceAdded')";
        mysqli_query(  $this->connection, $query )
        or die(mysqli_error($this->connection));

        SwitchHandler::addMac($mac, $matricula);
        return $deviceAdded;
    }

    private function deleteDevice($request){

        $this->connect( 'root','2016rys#.' );
        $mac = $request['mac'];
        $id = $request['id'];
        $query = "DELETE FROM dispositivos WHERE mac='$mac'";
        mysqli_query(  $this->connection, $query )
        or die(mysqli_error($this->connection));

        $query = "DELETE FROM pertenencias WHERE idDispositivo='$id'";
        mysqli_query(  $this->connection, $query )
        or die(mysqli_error($this->connection));

        SwitchHandler::deleteMac($mac);
        return true;

    }

    private function updateDevice($request){

      $this->connect('root','2016rys#.');
      $id = $request['id'];
      $mac = $request['mac'];
      $oldMac = $this->getDevice($id)['mac'];
      $alias = $request['alias'];

      $query = "UPDATE dispositivos SET mac='$mac', nombre='$alias' WHERE idDispositivo='$id'";
      mysqli_query(  $this->connection, $query )
      or die(mysqli_error($this->connection));

        SwitchHandler::deleteMac($oldMac);
        SwitchHandler::addMac($mac);
       return true;

    }

    public function connect( $username, $password = null ) {

        $isPasswordDefined = ! is_null( $password );

        if ( $isPasswordDefined ) {

            $this->connection = mysqli_connect( $this->server, $username, $password, $this->name );

        } else {

            $this->connection = mysqli_connect( $this->server, $username, $this->name );

        }

        if ( $this->connection ) {

            mysqli_select_db( $this->connection, $this->name );
            $isConnectionEstablished = true;

        } else {

            $isConnectionEstablished = false;

        }

        return $isConnectionEstablished;

    }


}


function Main() {

    $communicationHandler = new CommunicationHandler();

    $communicationHandler->receiveRequest($_POST);



}

Main();
