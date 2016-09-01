<?php
/**
 * Created by PhpStorm.
 * User: Hai
 * Date: 2016/1/12
 * Time: 15:35
 */

    header('Content-type:text/json');
   
    //文件本身是UTF-8，直接输出语句是UTF-8格式，函数、数据库返回的值则是是GBK格式

    /*apikey设置和验证*/
    $api_pass='qwerty!!!';

    $api_key=$_GET['apikey'];
    
    /*3次md5加密*/
    $option=strcmp(md5(md5(md5($api_pass))),$api_key);
    
    if ($option!==0){
        
        echo "Illegal Access!";
        exit();
        
    } 


    /*接收参数*/
    $action=$_GET['action'];
    
    /*接收参数*/
    switch($action) {

        /*  根据用户名获取用户信息  */
        case "get_user_info":

            try {

                //require_once "IPLimit.php";

                $q_username=$_GET['username'];

                $q_username=iconv("utf-8", "gb2312", $q_username);

                $dsn = "DRIVER=Microsoft Access Driver (*.mdb);dbq=" . realpath("../db/rtxdb.mdb");

                $conn = @odbc_connect($dsn, "", "", SQL_CUR_USE_ODBC);

                $sql = "select username,name,mobile,phone from Sys_user where (username='$q_username') and (AccountState<>1 or AccountState is null)";

                $rs = @odbc_do($conn, $sql);

                $result = array();

                $username = odbc_result($rs, "username");

                $result[] = iconv("gb2312", "utf-8", $username);

                $name = odbc_result($rs, "name");

                $result[] = iconv("gb2312", "utf-8", $name);

                $result[]   = odbc_result($rs, "mobile");

                $result[]    = odbc_result($rs, "phone");

                /*用户名唯一，不用数组*/
                //array_push($result, array('username' => $username,'name' => $name,'mobile' => $mobile,'phone' => $phone));
               
                if (!$result){

                    throw new Exception('error');

                }
            
                echo json_encode($result);
                
            }catch(Exception $e){
                
                echo $e->getMessage();
                
            }   
 
            
        break;




        /*  根据用户姓名获取用户信息  */
        case "get_user_info_by_name":

            try {

                    //require_once "IPLimit.php";

                    $q_name=$_GET['name'];

                    $q_name=iconv("utf-8", "gb2312", $q_name);

                    $dsn = "DRIVER=Microsoft Access Driver (*.mdb);dbq=" . realpath("../db/rtxdb.mdb");

                    $conn = @odbc_connect($dsn, "", "", SQL_CUR_USE_ODBC);

                    $sql = "select username,name,mobile,phone from Sys_user where (name='$q_name') and (AccountState<>1 or AccountState is null)";

                    $rs = @odbc_do($conn, $sql);

                    $result = array();

                    while (odbc_fetch_row($rs)) {

                        $username = odbc_result($rs, "username");

                        $username = iconv("gb2312", "utf-8", $username);

                        $name = odbc_result($rs, "name");

                        $name = iconv("gb2312", "utf-8", $name);

                        $mobile   = odbc_result($rs, "mobile");

                        $phone    = odbc_result($rs, "phone");

                        array_push($result, array('username' => $username,'name' => $name,'mobile' => $mobile,'phone' => $phone));
                    }
                   
                    /*if (!$result){

                        throw new Exception('error');

                    }*/
                
                    odbc_close($conn);

                    echo json_encode($result);
                
            
            }catch(Exception $e){
                
                echo $e->getMessage();
                
            }   
 

        break;




        /*  获取所有用户信息  */
        case "get_all_user_info":

            try {

                //require_once "IPLimit.php";
                $dsn = "DRIVER=Microsoft Access Driver (*.mdb);dbq=" . realpath("../db/rtxdb.mdb");

                $conn = @odbc_connect($dsn, "", "", SQL_CUR_USE_ODBC);

                $sql = "select id, username,name,mobile from Sys_user where (AccountState<>1 or AccountState is null) order by name ASC";

                $rs = @odbc_do($conn, $sql);

                $result = array();

                while (odbc_fetch_row($rs)) {

                    $id = odbc_result($rs, "id");

                    $username = odbc_result($rs, "username");

                    $username = iconv("gb2312", "utf-8", $username);

                    $name = odbc_result($rs, "name");

                    $name = iconv("gb2312", "utf-8", $name);

                    $mobile = odbc_result($rs, "mobile");

                    $mobile = substr($mobile, 0, 11);

                    array_push($result, array('id' => $id,'username' => $username,'name' => $name, 'mobile' => $mobile));

                }

                if (!$result){

                    throw new Exception('error!');

                }
                
                odbc_close($conn);

                echo json_encode($result);
                
                
            }catch(Exception $e){
                
                echo $e->getMessage();
                
            }

            
        break;




        /*  获取所有群信息  */
        case "get_all_group_info":

            try {

                //require_once "IPLimit.php";
                $dsn = "DRIVER=Microsoft Access Driver (*.mdb);dbq=" . realpath("../db/DisGroup_Db.mdb");

                $conn = @odbc_connect($dsn, "", "", SQL_CUR_USE_ODBC);

                $sql = "select groupid,groupname from GroupBasicInfo where IsEffective=1 order by groupid ASC";

                $rs = @odbc_do($conn, $sql);

                $result = array();

                while (odbc_fetch_row($rs)) {

                    $groupid = odbc_result($rs, "groupid");

                    $groupname = odbc_result($rs, "groupname");

                    $groupname = iconv("gb2312", "utf-8", $groupname);


                    array_push($result, array('groupid' => $groupid, 'groupname' => $groupname));

                }

                if (!$result){

                    throw new Exception('error');

                }
                
                odbc_close($conn);

                echo json_encode($result);
                
                
            }catch(Exception $e){
                
                echo $e->getMessage();
                
            }
  

        break;



        
        /*  根据群ID获取用户列表  */
        case "get_group_user_list":

            try {

                //require_once "IPLimit.php";

                $groupid=$_GET['groupid'];

                //创建根对象
                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                //通过根对象创建群管理对象
                $DisGroupManager=$RTXObj->DisGroupManager;
  
                //群管理方法
                $result=$DisGroupManager->GetDisGroupUsers($groupid);

                //转换编码
                $result=iconv('GBK', 'UTF-8', $result);
                
                //返回结果是xml字符串
                $result=simplexml_load_string($result); 
                
                if (!$result){

                    throw new Exception('error');

                }
            
                echo json_encode($result);
                
            
            }catch(Exception $e){
                
                echo iconv('GBK', 'UTF-8',$e->getMessage());
                
            }   
  

        break;




        /*  从群中删除指定的用户 */
        case "del_group_user":
            
            try {

                $username=$_GET['username'];

                $username_gbk=iconv('UTF-8', 'GBK', $username);
                
                $groupid=$_GET['groupid'];

                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $DisGroupManager=$RTXObj->DisGroupManager;

                $result=$DisGroupManager->DelUserFromDisGroup($groupid,$username_gbk);
                
                if ($result){

                        throw new Exception('error');

                    }

                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;


            }catch(Exception $e){
            
                echo iconv('GBK', 'UTF-8',$e->getMessage());
            
            }  


        break;




        /*  添加用户到群 */
        case "add_group_user":
            
            try {

                $username=$_GET['username'];

                $username_gbk=iconv('UTF-8', 'GBK', $username);
                
                $groupid=$_GET['groupid'];

                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $DisGroupManager=$RTXObj->DisGroupManager;

                $result=$DisGroupManager->AddUserToDisGroup($groupid,$username_gbk);
                
                if ($result){

                        throw new Exception('error');

                    }
                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;
                

            }catch(Exception $e){
            
                echo iconv('GBK', 'UTF-8',$e->getMessage());
            
            }  


        break;




        /*  添加群 */
        case "add_group":
            
            try {

                $groupname=$_GET['groupname'];

                $groupname_gbk=iconv('UTF-8', 'GBK', $groupname);
        
                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $DisGroupManager=$RTXObj->DisGroupManager;

                $result=$DisGroupManager->AddDisGroup($groupname_gbk);
                
                if (!$result){

                        throw new Exception('error');

                    }
                    
                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;
                

            }catch(Exception $e){
            
                echo iconv('GBK', 'UTF-8',$e->getMessage());
            
            }  


        break;




        /*  删除群 */
        case "del_group":
            
            try {

                $groupname=$_GET['groupname'];

                $groupname_gbk=iconv('UTF-8', 'GBK', $groupname);
        
                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $DisGroupManager=$RTXObj->DisGroupManager;

                //根据群名称查找群ID

                $groupid=$DisGroupManager->GetDisGroupIdsByName($groupname_gbk);

                $arry=explode('"',$groupid);

                $groupid=$arry[1];

                //执行群删除操作
                $result=$DisGroupManager->DelDisGroup($groupid);
                
                if ($result){

                        throw new Exception('error');

                    }

                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;
                

            }catch(Exception $e){
            
                echo iconv('GBK', 'UTF-8',$e->getMessage());
            
            }  


        break;




        /*  修改群 */
        case "edit_group":
            
            try {

                $old_groupname=$_GET['old_groupname'];

                $old_groupname_gbk=iconv('UTF-8', 'GBK', $old_groupname);

                $new_groupname=$_GET['new_groupname'];

                $new_groupname_gbk=iconv('UTF-8', 'GBK', $new_groupname);
        
                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $DisGroupManager=$RTXObj->DisGroupManager;

                //根据群名称查找群ID

                $groupid=$DisGroupManager->GetDisGroupIdsByName($old_groupname_gbk);

                $arry=explode('"',$groupid);

                $groupid=$arry[1];


                //执行群修改操作
                $result=$DisGroupManager->SetDisGroupName($groupid,$new_groupname_gbk);
                
                if ($result){

                        throw new Exception('error');

                    }

                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;
                

            }catch(Exception $e){
            
                echo iconv('GBK', 'UTF-8',$e->getMessage());
            
            }  


        break;




        /*  获取组织架构 */
        case "get_org":
            
            try {

                //require_once "IPLimit.php";
                $dsn = "DRIVER=Microsoft Access Driver (*.mdb);dbq=" . realpath("../db/rtxdb.mdb");

                $conn = @odbc_connect($dsn, "", "", SQL_CUR_USE_ODBC);

                $sql = "select deptid,deptname,pdeptid from RTX_Dept order by pdeptid ASC,sortid ASC";

                $rs = @odbc_do($conn, $sql);

                $result = array();

                while (odbc_fetch_row($rs)) {

                    $deptid = odbc_result($rs, "deptid");

                    $pdeptid = odbc_result($rs, "pdeptid");

                    $deptname = odbc_result($rs, "deptname");

                    $deptname = iconv("gb2312", "utf-8", $deptname);

                    array_push($result, array('id' => $deptid,'name' => $deptname,'parent_id' => $pdeptid));

                }


                if (!$result){

                        throw new Exception('error');

                    }

                $result=json_encode($result);

                echo $result;
                

            }catch(Exception $e){
            
                echo iconv('GBK', 'UTF-8',$e->getMessage());
            
            }  


        break;




        /*  添加新用户并设置到部门 */
        case "add_user":
            
            try {
    
                $username=$_GET['username'];
                $username_gbk=iconv("UTF-8", "GBK", $username);

                $name=$_GET['name'];
                $name_gbk=iconv("UTF-8", "GBK", $name);

                $password=$_GET['password'];

                $deptname=$_GET['deptname'];
                $deptname_gbk=iconv("UTF-8", "GBK", $deptname);

                $sex=$_GET['sex'];
                
                $mobile=$_GET['mobile'];

                $phone=$_GET['phone'];

                $mail="";

                $authtype="0";

                //添加用户
                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $AddUserBase=$RTXObj->UserManager;

                $result=$AddUserBase->AddUser($username_gbk,0);

                //设置用户密码
                //[in]bstrUserName 用户名
                //[in]bstrPwd 用户密码
                $result=$AddUserBase->SetUserPwd($username_gbk,$password);

                //设置用户简单资料
                //[in]bstrUserName 用户名
                //[in]bstrName 用户姓名
                //[in]lGender 用户性别
                //[in]bstrMobile 用户手机
                //[in]bstrEMail 用户电子邮件
                //[in]bstrPhone 用户电话
                //[in]lAuthType 用户认证类型
                //所有参数一定要以变量形式传递
                $result=$AddUserBase->SetUserBasicInfo($username_gbk,$name_gbk,$sex,$mobile,$mail,$phone,$authtype);

                //添加用户到部门
                $MoveUserToDept=$RTXObj->DeptManager;

                $soudeptname="";

                //[in] bstrUserName  用户名 
                //[in] bstrSrcDeptName  用户原来所在部门名字，如果部门的名称不唯一，则必须是绝 对路径名。 
                //[in] bstrDestDeptName  用户将被添加到的部门名字，如果部门的名称不唯一，则必须是绝 对路径名。
                //[in] bIsCopy  是否采用拷贝的方式。如采用拷贝方式则用户将在原来的部门中保留，否则用户将从原来所在的部门中删除。 
                $result=$MoveUserToDept->AddUserToDept($username_gbk,$soudeptname,$deptname_gbk,False);


                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;

                
            }catch(Exception $e){

                echo iconv('GBK', 'UTF-8',$e->getMessage());
                
            }

            
        break;




        /*  根据部门名称查看部门所有用户 */
        case "get_dept_user_list":
            
            try {

                //require_once "IPLimit.php";

                $deptname=$_GET['deptname'];

                $deptname_gbk=iconv('UTF-8','GBK', $deptname);


                $dsn = "DRIVER=Microsoft Access Driver (*.mdb);dbq=" . realpath("../db/rtxdb.mdb");

                $conn = @odbc_connect($dsn, "", "", SQL_CUR_USE_ODBC);

                $sql = "select Sys_user.username, Sys_user.name,RTX_Dept.DeptName from RTX_Dept,Sys_user,RTX_DeptUser where (RTX_DeptUser.userID=Sys_user.ID) and (RTX_Dept.DeptID=RTX_DeptUser.DeptID) and (RTX_Dept.DeptName='$deptname_gbk')  order by Sys_user.name ASC";

                $rs = @odbc_do($conn, $sql);

                $result = array();

                while (odbc_fetch_row($rs)) {

                    $username = odbc_result($rs, "username");

                    $username = iconv("gb2312", "utf-8", $username);

                    $name = odbc_result($rs, "name");

                    $name = iconv("gb2312", "utf-8", $name);

                    $deptname = odbc_result($rs, "deptname");

                    $deptname = iconv("gb2312", "utf-8", $deptname);

                    array_push($result, array('DeptName' => $deptname,'username' => $username,'name' => $name));

                }

                /*使用API 例外一种方法*/
                
                /*//创建根对象
                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                //通过根对象创建部门管理对象
                $DisDeptManager=$RTXObj->DeptManager;
  
                //部门管理方法
                $result=$DisDeptManager->GetDeptUsers($deptname_gbk);

                //转换编码
                $result=iconv('GBK', 'UTF-8', $result);
                
                $result=simplexml_load_string($result); */
                
                if (!$result){

                    throw new Exception('error');

                }
            
                echo json_encode($result);
                
            
            }catch(Exception $e){
                
                echo iconv('GBK', 'UTF-8',$e->getMessage());
                
            }   

            
        break;




        /*添加用户到部门*/
        case "add_dept_user":

            try{

                $desdeptname=$_GET['deptname'];

                $desdeptname_gbk=iconv('UTF-8','GBK', $desdeptname);

                $username=$_GET['username'];

                $username_gbk=iconv('UTF-8','GBK', $username);

                $soudeptname=$_GET['soudeptname'];

                $soudeptname_gbk=iconv('UTF-8','GBK', $soudeptname);

                $copyorcut=$_GET['copyorcut'];

                /*echo $desdeptname.'<br>';
                echo $soudeptname.'<br>';
                echo $username.'<br>';
                echo $copyorcut.'<br>';*/

                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $MoveUserToDept=$RTXObj->DeptManager;


                //[in] bstrUserName  用户名 
                //[in] bstrSrcDeptName  用户原来所在部门名字，如果部门的名称不唯一，则必须是绝 对路径名。 
                //[in] bstrDestDeptName  用户将被添加到的部门名字，如果部门的名称不唯一，则必须是绝 对路径名。
                //[in] bIsCopy  是否采用拷贝的方式。如采用拷贝方式则用户将在原来的部门中保留，否则用户将从原来所在的部门中删除。
                $result=$MoveUserToDept->AddUserToDept($username_gbk,$soudeptname_gbk,$desdeptname_gbk,$copyorcut);


                if ($result){

                    throw new Exception('error');

                }


                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;

            
            }catch(Exception $e){
            
            echo iconv('GBK', 'UTF-8',$e->getMessage());
            
        }   


        break;




        /*删除用户*/
        case "del_user":

            try{

                $username=$_GET['username'];

                $username_gbk=iconv('UTF-8','GBK', $username);

                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $DisDelUser=$RTXObj->UserManager;

                $result=$DisDelUser->DeleteUser($username_gbk);


                if ($result){

                    throw new Exception('error');

                }

                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;

            
            }catch(Exception $e){
            
            echo iconv('GBK', 'UTF-8',$e->getMessage());
            
        }   


        break;




        /*更改密码*/
        case "change_password":

            try{

                $username=$_GET['username'];

                $username_gbk=iconv('UTF-8','GBK', $username);

                $password=$_GET['password'];

                $RTXObj=new COM('RTXSAPIRootObj.RTXSAPIRootObj')or die('not found the COMOBJ');

                $ChangeUser=$RTXObj->UserManager;

                //设置用户密码
                //[in]bstrUserName 用户名
                //[in]bstrPwd 用户密码
                $result=$ChangeUser->SetUserPwd($username_gbk,$password);

                if ($result){

                    throw new Exception('error');

                }

                //api返回结果为空，自定义返回字符串
                $result="ok";
                echo $result;

            
            }catch(Exception $e){
            
            echo iconv('GBK', 'UTF-8',$e->getMessage());
            
        }   


        break;



               
        /*  发送通知  */
        case "send_notify":

            header("Content-Type:text/html;charset=UTF-8");

            //require_once "IPLimit.php";

            //获取参数，先GET方式，GET获取不到就POST方式

            $receiver = $_GET["receiver"];

            $receiver= json_decode($receiver);

            //var_dump($receiver->username);
            


            /*把接收的用户名转换成数组存放*/
            $username=array();

            //echo sizeof($receiver->username);
          
            foreach ($receiver->username as $value) {

                $username[]=iconv("utf-8","gbk", $value);
                
            }

            

            /*文本有换行符，编码传输后，需要解码*/
            $msg = $_GET["msg"];
            $msg = urldecode($msg);


            $title = $_GET["title"];

            $msg = iconv("utf-8","gbk", $msg);

            $title = iconv("utf-8","gbk", $title);

            /*var_dump($username);

            exit();*/

            $delaytime = $_GET["delaytime"];
            

            if (strlen($username) == 0)
            {
                $username = "";
            }
            if(strlen($msg) == 0) 
            {
                $msg = "";
            }
            if(strlen($title) == 0)
            {
                $title = "";
            }
            if(strlen($delaytime) == 0)
            {
                $delaytime = 0;
            }



            //调用API开始发送

            $php_errormsg = NULL;

            $ObjApi= new COM("Rtxserver.rtxobj");
            $objProp= new COM("Rtxserver.collection");
            $Name = "ExtTools";
            $ObjApi->Name = $Name;

            
            /*循环发送消息给用户*/

            $i=0; //用户名数组下标

            while ($i<sizeof($username)) {

                $objProp->Add("msgInfo", $msg);
                $objProp->Add("MsgID", "1");
                $objProp->Add("Type", "0");
                $objProp->Add("AssType", "0");

                /*发送标题*/
                if (strlen($title) == 0)
                {
                    $title=iconv("utf-8","gbk", "通知");

                    $objProp->Add("Title", $title);
                }
                else
                {
                    $objProp->Add("Title", $title);
                }

                /*存在的时间*/
                $objProp->Add("DelayTime", $delaytime);


                /*发送对象*/

                /*判断是否是所有用户*/

                if (strtolower($username[0]) == "all")
                {
                    $objProp->Add("Username", $username[0]);
                    $objProp->Add("SendMode", "1");
                }
                else
                {
                    $objProp->Add("Username", $username[$i]);
                }

                $Result = @$ObjApi->Call2(0x2100, $objProp);

                $i++;

            }


            


            /*  判断结果是否出错  */
            $errstr = $php_errormsg;
            
            if(strcmp($nullstr, $errstr) == 0)
            {
                    echo "ok";
                
            }
            else
            {
                
                    echo $errstr."<br>";
                    
            }


        break;

    }


?>
