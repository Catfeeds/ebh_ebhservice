<?php
//操作日志描述
$logdescription = array(
	//----------课程
	'addcourse' => array('message'=>'添加课程《<color>[iname]<color>》价格：[iprice]元',
					'opid'=>1,'type'=>'folder','typestr'=>'添加课程'),
	
	'delcourse' => array('message'=>'删除课程《<color>[iname]<color>》',
					'opid'=>4,'type'=>'folder','typestr'=>'删除课程'),

    'editcourse' => array('message'=>'原课程《<color>[inameold]<color>》价格：[ipriceold]元，改为：《<color>[iname]<color>》价格：[iprice]元',
                    'opid'=>2,'type'=>'folder','typestr'=>'编辑课程'),
	//-----------课件
	'addcw' => array('message'=>'在课程《<color>[iname]<color>》中添加课件《<color>[title]<color>》价格：[iprice]元',
					'opid'=>1,'type'=>'courseware','typestr'=>'添加课件'),
	
	'delcw' => array('message'=>'在课程《<color>[iname]<color>》中删除课件《<color>[title]<color>》',
					'opid'=>4,'type'=>'courseware','typestr'=>'删除课件'),
	
	'editcw' => array('message'=>'将课程《<color>[iname]<color>》中课件《<color>[titleold]<color>》价格：[ipriceold]元，改为：《<color>[title]<color>》价格：[iprice]元',
					'opid'=>2,'type'=>'courseware','typestr'=>'编辑课件'),
	//------------学生/员工
	'addstudent' => array('message'=>'添加[student]：[username]，[realname]，[sex]',
					'opid'=>1,'type'=>'roomuser','typestr'=>'添加[student]'),
	
	'delstudent' => array('message'=>'删除[student]：[username]，[realname]，[sex]',
					'opid'=>4,'type'=>'roomuser','typestr'=>'删除[student]'),
	
	'studentpass' => array('message'=>'重置[student]：[username]，[realname]，[sex]。密码为：[password]',
					'opid'=>2,'type'=>'roomuser','typestr'=>'重置[student]密码'),
	
	'multistudent' => array('message'=>'批量导入[student]：[usercount]人。[usernames] 等',
					'opid'=>1,'type'=>'roomuser','typestr'=>'批量导入[student]'),
	
	'lockstudent' => array('message'=>'禁用[student]：[username]，[realname]，[sex]',
					'opid'=>2,'type'=>'roomuser','typestr'=>'禁用[student]'),
	
	'unlockstudent' => array('message'=>'解除禁用[student]：[username]，[realname]，[sex]',
					'opid'=>2,'type'=>'roomuser','typestr'=>'解除禁用[student]'),
	//-------------老师/讲师
	'addteacher' => array('message'=>'添加[teacher]：[username]，[realname]，[sex]',
					'opid'=>1,'type'=>'teacher','typestr'=>'添加[teacher]'),
	
	'delteacher' => array('message'=>'删除[teacher]：[username]，[realname]，[sex]',
					'opid'=>4,'type'=>'teacher','typestr'=>'删除[teacher]'),
	
	'teacherpass' => array('message'=>'重置[teacher]：[username]，[realname]，[sex]。密码为：[password]',
					'opid'=>2,'type'=>'teacher','typestr'=>'重置[teacher]密码'),
	
	'multiteacher' => array('message'=>'批量导入[teacher]：[usercount]人。[usernames] 等',
					'opid'=>1,'type'=>'teacher','typestr'=>'批量导入[teacher]'),
	
	'lockteacher' => array('message'=>'禁用[teacher]：[username]，[realname]，[sex]',
					'opid'=>2,'type'=>'teacher','typestr'=>'禁用[teacher]'),
	
	'unlockteacher' => array('message'=>'解除禁用[teacher]：[username]，[realname]，[sex]',
					'opid'=>2,'type'=>'teacher','typestr'=>'解除禁用[teacher]'),
					
	//-------------分销
    'changeisshare'=> array('message'=>'[changestatus] 分销功能',
                    'opid'=>2,'type'=>'classroom','typestr'=>'开关分销功能'),

    'setsharepercent'=> array('message'=>'设置通用分销比为[sharepercent]%',
                    'opid'=>2,'type'=>'classroom','typestr'=>'设置通用分销比'),

    'addusershare'=> array('message'=>'共添加[usernames] 等 [count]个用户, 分销比例为[percent]%',
                    'opid'=>1,'type'=>'classroom','typestr'=>'添加用户分销比例'),

    'editusershare'=>array('message'=>'修改用户[username]，[realname]，[sex], 原分销比例[oldpercent]%改为[newpercent]%',
                    'opid'=>2,'type'=>'classroom','typestr'=>'编辑用户分销比例'),

    'delusershare'=>array('message'=>'删除用户[username]，[realname]，[sex], 分销比例[percent]%',
                    'opid'=>4,'type'=>'classroom','typestr'=>'删除用户分销比例'),

    //-------------作业
    'addexam'=> array('message'=>'用户: [username] 发布作业: [title]',
                    'opid'=>1,'type'=>'exam','typestr'=>'发布作业'),

    'editexam'=>array('message'=>'用户: [username] 编辑了作业: [title]',
                    'opid'=>2,'type'=>'exam','typestr'=>'编辑作业'),

    'delexam'=>array('message'=>'用户: [username] 删除作业: [title]',
                    'opid'=>4,'type'=>'exam','typestr'=>'删除作业'),
    //-------------首页装扮
    'adddesign'=> array('message'=>'添加[clientType]版装扮: [title]',
        'opid'=>1,'type'=>'design','typestr'=>'添加装扮'),

    'editdesign'=>array('message'=>'将[clientType]版装扮: [oldtitle] 修改为: [title]',
        'opid'=>2,'type'=>'design','typestr'=>'编辑装扮'),

    'deldesign'=>array('message'=>'删除[clientType]版装扮: [title]',
        'opid'=>4,'type'=>'design','typestr'=>'删除装扮'),

    'savedesign'=>array('message'=>'[clientType]版装扮: [title] 已保存',
        'opid'=>2,'type'=>'design','typestr'=>'保存装扮'),

    'choosedesign'=>array('message'=>'启用[clientType]版装扮: [title]',
        'opid'=>2,'type'=>'design','typestr'=>'启用装扮'),
);
return $logdescription;
?>