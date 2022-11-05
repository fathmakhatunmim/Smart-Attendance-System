<?php
session_start();
ini_set('display_errors', 1);
Class Action {
	private $db;

	public function __construct() {
		ob_start();
   	include 'db_connect.php';
    
    $this->db = $conn;
	}
	function __destruct() {
	    $this->db->close();
	    ob_end_flush();
	}

	function login(){
		extract($_POST);		
		$qry = $this->db->query("SELECT * FROM users where username = '".$username."' and password = '".md5($password)."' ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'passwors' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
				return 1;
		}else{
			return 3;
		}
	}
	function login2(){
		
			extract($_POST);
			if(isset($email))
				$username = $email;
		$qry = $this->db->query("SELECT * FROM users where username = '".$username."' and password = '".md5($password)."' ");
		if($qry->num_rows > 0){
			foreach ($qry->fetch_array() as $key => $value) {
				if($key != 'passwors' && !is_numeric($key))
					$_SESSION['login_'.$key] = $value;
			}
			if($_SESSION['login_alumnus_id'] > 0){
				$bio = $this->db->query("SELECT * FROM alumnus_bio where id = ".$_SESSION['login_alumnus_id']);
				if($bio->num_rows > 0){
					foreach ($bio->fetch_array() as $key => $value) {
						if($key != 'passwors' && !is_numeric($key))
							$_SESSION['bio'][$key] = $value;
					}
				}
			}
			if($_SESSION['bio']['status'] != 1){
					foreach ($_SESSION as $key => $value) {
						unset($_SESSION[$key]);
					}
					return 2 ;
					exit;
				}
				return 1;
		}else{
			return 3;
		}
	}
	function logout(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:login.php");
	}
	function logout2(){
		session_destroy();
		foreach ($_SESSION as $key => $value) {
			unset($_SESSION[$key]);
		}
		header("location:../index.php");
	}

	function save_user(){
		extract($_POST);
		$data = " name = '$name' ";
		$data .= ", username = '$username' ";
		if(!empty($password))
		$data .= ", password = '".md5($password)."' ";
		$data .= ", type = '$type' ";
		if($type == 1)
			$establishment_id = 0;
		$data .= ", establishment_id = '$establishment_id' ";
		$chk = $this->db->query("Select * from users where username = '$username' and id !='$id' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO users set ".$data);
		}else{
			$save = $this->db->query("UPDATE users set ".$data." where id = ".$id);
		}
		if($save){
			return 1;
		}
	}
	function delete_user(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM users where id = ".$id);
		if($delete)
			return 1;
	}
	function signup(){
		extract($_POST);
		$data = " name = '".$firstname.' '.$lastname."' ";
		$data .= ", username = '$email' ";
		$data .= ", password = '".md5($password)."' ";
		$chk = $this->db->query("SELECT * FROM users where username = '$email' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
			$save = $this->db->query("INSERT INTO users set ".$data);
		if($save){
			$uid = $this->db->insert_id;
			$data = '';
			foreach($_POST as $k => $v){
				if($k =='password')
					continue;
				if(empty($data) && !is_numeric($k) )
					$data = " $k = '$v' ";
				else
					$data .= ", $k = '$v' ";
			}
			if($_FILES['img']['tmp_name'] != ''){
							$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
							$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
							$data .= ", avatar = '$fname' ";

			}
			$save_alumni = $this->db->query("INSERT INTO alumnus_bio set $data ");
			if($data){
				$aid = $this->db->insert_id;
				$this->db->query("UPDATE users set alumnus_id = $aid where id = $uid ");
				$login = $this->login2();
				if($login)
				return 1;
			}
		}
	}
	function update_account(){
		extract($_POST);
		$data = " name = '".$firstname.' '.$lastname."' ";
		$data .= ", username = '$email' ";
		if(!empty($password))
		$data .= ", password = '".md5($password)."' ";
		$chk = $this->db->query("SELECT * FROM users where username = '$email' and id != '{$_SESSION['login_id']}' ")->num_rows;
		if($chk > 0){
			return 2;
			exit;
		}
			$save = $this->db->query("UPDATE users set $data where id = '{$_SESSION['login_id']}' ");
		if($save){
			$data = '';
			foreach($_POST as $k => $v){
				if($k =='password')
					continue;
				if(empty($data) && !is_numeric($k) )
					$data = " $k = '$v' ";
				else
					$data .= ", $k = '$v' ";
			}
			if($_FILES['img']['tmp_name'] != ''){
							$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
							$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
							$data .= ", avatar = '$fname' ";

			}
			$save_alumni = $this->db->query("UPDATE alumnus_bio set $data where id = '{$_SESSION['bio']['id']}' ");
			if($data){
				foreach ($_SESSION as $key => $value) {
					unset($_SESSION[$key]);
				}
				$login = $this->login2();
				if($login)
				return 1;
			}
		}
	}

	function save_settings(){
		extract($_POST);
		$data = " name = '".str_replace("'","&#x2019;",$name)."' ";
		$data .= ", email = '$email' ";
		$data .= ", contact = '$contact' ";
		$data .= ", about_content = '".htmlentities(str_replace("'","&#x2019;",$about))."' ";
		if($_FILES['img']['tmp_name'] != ''){
						$fname = strtotime(date('y-m-d H:i')).'_'.$_FILES['img']['name'];
						$move = move_uploaded_file($_FILES['img']['tmp_name'],'assets/uploads/'. $fname);
					$data .= ", cover_img = '$fname' ";

		}
		
		// echo "INSERT INTO system_settings set ".$data;
		$chk = $this->db->query("SELECT * FROM system_settings");
		if($chk->num_rows > 0){
			$save = $this->db->query("UPDATE system_settings set ".$data);
		}else{
			$save = $this->db->query("INSERT INTO system_settings set ".$data);
		}
		if($save){
		$query = $this->db->query("SELECT * FROM system_settings limit 1")->fetch_array();
		foreach ($query as $key => $value) {
			if(!is_numeric($key))
				$_SESSION['system'][$key] = $value;
		}

			return 1;
				}
	}

	
	function save_course(){
		extract($_POST);
		$data = " course = '$course' ";
		$data .= ", description = '$description' ";
		$check = $this->db->query("SELECT * FROM courses where course = '$course' ".(!empty($id) ? ' and id!=$id ' : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
			if(empty($id)){
				$save = $this->db->query("INSERT INTO courses set $data");
			}else{
				$save = $this->db->query("UPDATE courses set $data where id = $id");
			}
		if($save)
			return 1;
	}
	function delete_course(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM courses where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_subject(){
		extract($_POST);
		$data = " subject = '$subject' ";
		$data .= ", description = '$description' ";
		$check = $this->db->query("SELECT * FROM subjects where subject = '$subject' ".(!empty($id) ? ' and id!=$id ' : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
			if(empty($id)){
				$save = $this->db->query("INSERT INTO subjects set $data");
			}else{
				$save = $this->db->query("UPDATE subjects set $data where id = $id");
			}
		if($save)
			return 1;
	}
	function delete_subject(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM subjects where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_class(){
		extract($_POST);
		$data = " course_id = '$course_id' ";
		$data .= ", level = '$level' ";
		$data .= ", section = '$section' ";
		$data2 = " course_id = '$course_id' ";
		$data2 .= "and level = '$level' ";
		$data2 .= "and section = '$section' ";

		$check = $this->db->query("SELECT * FROM class where $data2 ".(!empty($id) ? ' and id!=$id ' : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
			if(empty($id)){
				
				$save = $this->db->query("INSERT INTO class set $data");
			}else{
				$save = $this->db->query("UPDATE class set $data where id = $id");
			}
		if($save)
			return 1;
	}
	function delete_class(){
		extract($_POST);
		$delete = $this->db->query("UPDATE class set status = 0 where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_faculty(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id','ref_code')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM faculty where id_no ='$id_no' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO faculty set $data");
			$nid=$this->db->insert_id;
		}else{
			$save = $this->db->query("UPDATE faculty set $data where id = $id");
		}

		if($save){
			$user = " name = '$name' ";
			$user .= ", username = '$email' ";
			$user .= ", password = '".(md5($id_no))."' ";
			$user .= ", type = 3 ";
			if(empty($id)){
			$user .= ", faculty_id = $nid ";
			$save = $this->db->query("INSERT INTO users set $user");

			}else{
			$save = $this->db->query("UPDATE users set $user where faculty_id = $id");
			}
			return 1;
		}
	}
	function delete_faculty(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM faculty where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_student(){
		extract($_POST);
		$data = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM students where id_no ='$id_no' ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO students set $data");
		}else{
			$save = $this->db->query("UPDATE students set $data where id = $id");
		}

		if($save){
			return 1;
		}
	}
	function delete_student(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM students where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function save_class_subject(){
		extract($_POST);
		$data = "";
		$data2 = "";
		foreach($_POST as $k => $v){
			if(!in_array($k, array('id')) && !is_numeric($k)){
				if(empty($data)){
					$data .= " $k='$v' ";
					$data2 .= " $k='$v' ";
				}else{
					$data .= ", $k='$v' ";
					$data2 .= "and $k='$v' ";
				}
			}
		}
		$check = $this->db->query("SELECT * FROM class_subject where $data2 ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
			$save = $this->db->query("INSERT INTO class_subject set $data");
		}else{
			$save = $this->db->query("UPDATE class_subject set $data where id = $id");
		}

		if($save){
			return 1;
		}
	}
	function delete_class_subject(){
		extract($_POST);
		$delete = $this->db->query("DELETE FROM class_subject where id = ".$id);
		if($delete){
			return 1;
		}
	}
	function get_class_list(){
		extract($_POST);
		$data = array();
		$get = $this->db->query("SELECT s.* FROM students s inner join `class` c on c.id = s.class_id inner join class_subject cs on cs.class_id = c.id where cs.id = '$class_subject_id' ");
		if(isset($att_id)){
			$record = $this->db->query("SELECT * FROM attendance_record where attendance_id='$att_id' ");
		if($record->num_rows > 0){
			while($row = $record->fetch_assoc()){
				$data['record'][] = $row;
				$data['attendance_id'] = $row['attendance_id'];
			}
		}
		}
		while($row = $get->fetch_assoc()){
			$data['data'][] = $row;
		}
		return json_encode($data);
	}
	function get_att_record(){
		extract($_POST);
		$get = $this->db->query("SELECT s.* FROM students s inner join `class` c on c.id = s.class_id inner join class_subject cs on cs.class_id = c.id where cs.id = '$class_subject_id' ");
		$record = $this->db->query("SELECT ar.*,a.class_subject_id FROM attendance_record ar inner join attendance_list a on a.id =ar.attendance_id where a.class_subject_id='$class_subject_id' and a.doc = '$doc' ");
		$data = array();
		while($row = $get->fetch_assoc()){
			$data['data'][] = $row;
		}
		if($record->num_rows > 0){
			while($row = $record->fetch_assoc()){
				$data['record'][] = $row;
				$data['attendance_id'] = $row['attendance_id'];
		}
		}
		$qry = $this->db->query("SELECT s.subject,co.course,concat(c.level,'-',c.section) as `class` FROM class_subject cs inner join class c on c.id = cs.class_id inner join subjects s on s.id = cs.subject_id inner join courses co on co.id = c.id where cs.id = {$class_subject_id} ");
		foreach($qry->fetch_array() as $k => $v){
			$data['details'][$k] =$v; 
		}
		$data['details']['doc'] =date('M d, Y',strtotime($doc)); 

		return json_encode($data);
	}
	function get_att_report(){
		extract($_POST);
		$get = $this->db->query("SELECT s.* FROM students s inner join `class` c on c.id = s.class_id inner join class_subject cs on cs.class_id = c.id where cs.id = '$class_subject_id' ");
		$record = $this->db->query("SELECT ar.*,a.class_subject_id FROM attendance_record ar inner join attendance_list a on a.id =ar.attendance_id where a.class_subject_id='$class_subject_id' and date_format(a.doc,'%Y-%m') = '$doc' ");
		$data = array();
		while($row = $get->fetch_assoc()){
			$data['data'][] = $row;
		}
		if($record->num_rows > 0){
			while($row = $record->fetch_assoc()){
				$data['record'][$row['student_id']][] = $row;
				$data['attendance_id'] = $row['attendance_id'];
		}
		}
		$noc = $this->db->query("SELECT * FROM attendance_list where class_subject_id='$class_subject_id' and date_format(doc,'%Y-%m') = '$doc' ");
				$data['details']['noc'] = $noc->num_rows;


				$qry = $this->db->query("SELECT s.subject,co.course,concat(c.level,'-',c.section) as `class` FROM class_subject cs inner join class c on c.id = cs.class_id inner join subjects s on s.id = cs.subject_id inner join courses co on co.id = c.id where cs.id = {$class_subject_id} ");
				foreach($qry->fetch_array() as $k => $v){
					$data['details'][$k] =$v; 
				}

		$data['details']['doc'] =date('F ,Y',strtotime($doc)); 

		return json_encode($data);
	}
	function save_attendance(){
		extract($_POST);
		$data  = " class_subject_id = '$class_subject_id' ";
		$data .= ", doc = '$doc' ";
		$data2  = " class_subject_id = '$class_subject_id' ";
		$data2 .= "and doc = '$doc' ";
		// echo "SELECT * FROM attendance_list where $data2 ".(!empty($id) ? " and attendance_id != {$id} " : '');
		$check = $this->db->query("SELECT * FROM attendance_list where $data2 ".(!empty($id) ? " and id != {$id} " : ''))->num_rows;
		if($check > 0){
			return 2;
			exit;
		}
		if(empty($id)){
				
				$save = $this->db->query("INSERT INTO attendance_list set $data ");
			if($save){
				$id = $this->db->insert_id;
				foreach($student_id as $k => $v) {
					$data = " attendance_id = '$id' ";
					$data .= ", student_id = '$k' ";
					$data .= ", type = '$type[$k]' ";
						  $this->db->query("INSERT INTO attendance_record set $data ");
				}
			}
		}else{
			$save = $this->db->query("UPDATE attendance_list set $data where id=$id ");
			if($save){
				foreach($student_id as $k => $v) {
					$data = " attendance_id = '$id' ";
					$data .= "and student_id = '$k' ";
						  $this->db->query("UPDATE attendance_record set type = '$type[$k]' where $data ");
				}
			}
		}

		if($save){
			return 1;
		}
	}
}