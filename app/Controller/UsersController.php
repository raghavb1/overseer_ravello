<?php

App::uses('Users', 'Lib');
App::uses('Ravello', 'Lib');

class UsersController extends AppController {

//    public $components = array('Users','Ravello');
    public $output = array('success' => false, 'message'=> '');

    public function view(){
        $this->autoRender = false;
        $users = new Users();
        $output = $users->getUser();
        pr($output);
    }

    public function approve($username, $approve = true){
        $this->autoRender = false;
        $users = new Users();
        $users->approveUser($username, $approve);
    }

    public function assignVM($username){
        $this->autoRender = false;
        $this->genericVMcreateAndUserUpdate($username,'assignVM');

    }

    public function approveAndAssignVM($username){
        $this->autoRender = false;
        $this->genericVMcreateAndUserUpdate($username,'approveUserAndAssignVM');
    }

    public function deleteVM($username){
        $this->autoRender = false;
        $users = new Users();
        $ravello = new Ravello();
        $user = $users->getUser($username);
        if($user != null && $user->user->vm_ip != ''){
            $ravello->deleteVMFromDesign($user->user->vm_ip_id);
            $vm = array("ip"=>"","vm_ip_id"=>"");
            $users->assignVM($username, $vm);
            $this->output['success'] = true;
            $this->output['message'] = 'VM deleted with IP '.$user->user->vm_ip;
        }
        else{
            $this->output['success'] = false;
            $this->output['message'] = 'No IP assigned';
        }
        echo json_encode($this->output);
    }

    private function genericVMcreateAndUserUpdate($username, $type){
        $users = new Users();

        $user = $users->getUser($username);
        if($user != null && $user->user->vm_ip == ""){
            $bufferVM = null;
            while($bufferVM == null) {
                $bufferVM = Cache::read('bufferVM');
            }
            Cache::write('bufferVM',null);
//            pr($bufferVM);
            $users->$type($username, $bufferVM);
            $this->output['success'] = true;
            $this->output['new'] = true;
            $this->output['message'] = 'VM created with IP '.$bufferVM['ip'];
            exec(Configure::read('Command.path'));
        }else{
            $this->output['success'] = true;
            $this->output['new'] = false;
            $this->output['message'] = 'VM already created with IP '.$user->user->vm_ip;
        }
        echo json_encode($this->output);
    }

}
