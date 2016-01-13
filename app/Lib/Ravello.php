<?php
/**
 * Created by PhpStorm.
 * User: raghav
 * Date: 06/01/16
 * Time: 2:28 PM
 */

App::uses('HttpSocket', 'Network/Http');
App::uses('Xml', 'Utility');
class Ravello {

    private $http;

    public function createVM() {


        $this->createHTTPConnection();

        #gets all the vms present
        $deployedVms = $this->getDeployedVms();
//        pr($deployedVms);

        #adds a new vm from template to design
        $designVm = $this->addVMToDesign();
//        pr($designVm);

//        die();

        #calculates diff in ip to get the latest vm
        $designVm['result'] = array_diff($designVm['result'], array("10.0.0.4"));
        $diff = array_diff($designVm['result'], $deployedVms['result']);
        #pr($diff);

        #gets the ip to assign to user
        $ip = array_pop($diff);
        #echo $ip;

        $key = array_search($ip, $designVm['result']);
        $vmId = $designVm['array']['application']['ns2:design']['ns2:vms'][$key]['@id'];

        $output = array("ip"=>$ip, "vm_ip_id"=>$vmId);
        $this->publishUpdates();

        return $output;


    }


    public function publishUpdates(){
        $data= array(
            'method'=>'post',
            'url'=>'https://cloud.ravellosystems.com/api/v1/applications/62521360/publishUpdates',
            'data'=>array(),
            'path'=>'',
            'request'=>array()
        );
        return $this->genericDataExtractor($data);

    }
    public function getDeployedVms(){
        $data= array(
            'method'=>'get',
            'url'=>'https://cloud.ravellosystems.com/api/v1/applications/62521360/vms',
            'data'=>array(),
            'path'=>'vms.ns1:designVm.{n}.ns1:networkConnections.ns1:ipConfig.ns1:autoIpConfig.@allocatedIp',
            'request'=>array()
        );
        return $this->genericDataExtractor($data);
    }

    public function addVMToDesign(){
        $data = array(
            'method'=>'post',
            'url'=>'https://cloud.ravellosystems.com/api/v1/applications/62521360/vms',
            'data'=>json_encode(array("baseVmId"=>Configure::read('Ravello.BaseVM.id'))),
            'path'=>'application.ns2:design.ns2:vms.{n}.ns2:networkConnections.ns2:ipConfig.ns2:autoIpConfig.@allocatedIp',
            'request'=>array(
                'header' => array(
                    'Content-Type' => 'application/json',
                    'Accept-Type'=>'application/json'
                ),
            )
        );


        return $this->genericDataExtractor($data);
    }

    public function deleteVMFromDesign($id){

        $this->createHTTPConnection();
        $data = array(
            'method'=>'delete',
            'url'=>'https://cloud.ravellosystems.com/api/v1/applications/62521360/vms/'.$id,
            'path'=>'',
            'data'=>array(),
            'request'=>array()
        );

        $this->genericDataExtractor($data);
        $this->publishUpdates();
    }
    public function genericDataExtractor($data){

        $output = array();
        $method = $data['method'];
        $result = $this->http->$method($data['url'],$data['data'], $data['request']);

        if($result->body != "") {
            $xmlArray = Xml::toArray(Xml::build($result->body));
            $output['result'] = Hash::extract($xmlArray, $data['path']);
            $output['array'] = $xmlArray;
        }
        return $output;
    }

    public function createHTTPConnection(){
        $this->http = new HttpSocket(array(
            'ssl_verify_peer' => false,
            'ssl_verify_host' => false
        ));
        $this->http->configAuth('Basic', Configure::read('Ravello.username'), Configure::read('Ravello.password'));
    }

}