#分期账单
CREATE TABLE `pf_loan_bill` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `lid` int(10) unsigned NOT NULL COMMENT '贷款id',
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '还款状态 0:未还款，1：正常还款，2逾期中，3提前还款  4:退课',
  `bill_date` varchar(7) NOT NULL COMMENT '账期',
  `installment` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '总期数',
  `installment_plan` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '该条期数',
  `principal` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '本金',
  `interest` decimal(10,2) unsigned NOT NULL DEFAULT '0.00' COMMENT '利息',
  `total` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总计还款金额',
  `should_repay_date` date NOT NULL COMMENT '应还日期',
  `interid` int(11) unsigned DEFAULT '0' COMMENT '融数还款单号',
  `repay_date` datetime DEFAULT NULL COMMENT '实际还款日期',
  `repay_principal` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '实际还款本金',
  `repay_interest` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '实际还款利息',
  `repay_total` decimal(10,2) DEFAULT '0.00' COMMENT '还款总额',
  `overdue_days` int(5) unsigned DEFAULT '0' COMMENT '逾期天数',
  `overdue_fine_interest` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '逾期利息',
  `overdue_fees` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '逾期手续费',
  `repay_overdue_fine_interest` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '已还罚息',
  `repay_overdue_fees` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '已还逾期手续费',
  `miss_principal` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '未还本金',
  `miss_interest` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '未还利息',
  `miss_overdue_fees` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '未还手续费',
  `miss_overdue_fine_interest` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '未还罚息',
  `miss_total` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '未还总额',
  `free_amount` decimal(10,2) DEFAULT '0.00' COMMENT '正值为用户多转金额；负值为大圣分期减免金额',
  `debit_way` tinyint(3) unsigned DEFAULT '0' COMMENT '0未扣款,1线上扣款成功,2线上扣款失败,3线上还款中,4逾期未还款,5X期提前还款成功,6全部提前还款成功,7线下还款成功',
  `rate_type` tinyint(2) unsigned DEFAULT '0' COMMENT '账单分期类型，1：弹性，2、贴息，3、等额',
  `xy` tinyint(2) unsigned DEFAULT '0' COMMENT '弹性专用，1x期，2y期，0默认无意义',
  `school_pay` tinyint(2) unsigned DEFAULT '1' COMMENT '机构代偿，1：非代偿，2：机构代偿',
	`interview_fee` decimal(10,2) unsigned DEFAULT '0.00' COMMENT '居间服务费',
  `pf_deduction` tinyint(4) NOT NULL DEFAULT '1' COMMENT '是否为大圣划扣，1否，2是',
  `fcs_service` decimal(10,2) DEFAULT NULL COMMENT '富登：应还服务费',
  `fcs_repay_service` decimal(10,2) DEFAULT NULL COMMENT '富登：实还服务费',
  `remark` varchar(255) NOT NULL COMMENT '备注',
  `resource` int(10) DEFAULT NULL,
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `lid` (`lid`),
  KEY `uid` (`uid`),
  KEY `status` (`status`),
  KEY `bill_date` (`bill_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='分期账单';

# 费率配置
CREATE TABLE `pf_loan_product` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增id',
    `resource` tinyint(2) unsigned DEFAULT '2' COMMENT '1、晋商;2、富登',
    `loan_product` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '费率id',
    `status` VARCHAR(20) CHARACTER SET utf8 NOT NULL DEFAULT 'SUCCESS' COMMENT '是否可以贷款 SUCCESS，可用，FAIL 不可用',
    `loan_ratio` decimal(10,4) unsigned NOT NULL DEFAULT '1.0000' COMMENT '放款比例',
    `loan_type` tinyint(3) unsigned NOT NULL COMMENT '费率类型，1=》弹性，2=》贴息',
    `loan_channel` tinyint(2) unsigned NOT NULL DEFAULT '0' COMMENT '放款类型',
    `repayment_day` tinyint(3) unsigned NOT NULL DEFAULT '15' COMMENT '还款日',
    `rate_time_x` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'X期',
    `rate_x` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'x期费率',
    `rate_time_y` tinyint(3) NOT NULL DEFAULT '0' COMMENT 'y期',
    `rate_y` decimal(10,4) NOT NULL DEFAULT '0.0000' COMMENT 'y期费率',
    `org_discount` decimal(10,4) unsigned DEFAULT '0.0000' COMMENT '机构贴息',
    `interview_fee` decimal(10,4) unsigned DEFAULT '0.0000' COMMENT '居间服务费费率',
    `real_rate` decimal(10,4) DEFAULT '0.0000' COMMENT '实际利率（年）（%）',
    `remark` varchar(30) CHARACTER SET utf8 NOT NULL COMMENT '优惠、正常、无保',
    `create_time` datetime NOT NULL ON UPDATE CURRENT_TIMESTAMP,
    `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `pay_loan_type_rateid_index` (`loan_product`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='费率配置';

# 用户银行卡
CREATE TABLE `pf_users_bank` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '索引ID',
    `uid` int(11) unsigned NOT NULL COMMENT '用户UID',
    `bank_account` varchar(30) NOT NULL COMMENT '银行卡号',
    `bank_name` varchar(30) NOT NULL COMMENT '银行',
    `phone` char(11) NOT NULL COMMENT '银行预留手机号',
    `status` varchar(30) NOT NULL DEFAULT 'SUCCESS' COMMENT '是否可用:SUCCESS 可用，FAIL:不可用',
    `type` tinyint(4) unsigned DEFAULT NULL COMMENT '卡类型，1，主动还款卡，2、签约卡',
    `protocol_no` varchar(200) DEFAULT NULL COMMENT '宝付签约协议号',
    `create_time` datetime DEFAULT NULL COMMENT '创建时间',
    `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '最后更新时间',
    PRIMARY KEY (`id`,`uid`,`bank_account`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT '用户银行卡列表';

# 用户活体检测记录表
CREATE TABLE `pf_users_auth_log` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(11) unsigned NOT NULL COMMENT '用户id',
    `lid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分期id',
    `order` varchar(64) NOT NULL COMMENT '订单号',
    `no_product` varchar(20) DEFAULT '' COMMENT 'F2001001；人脸身份认证',
    `result_auth` tinyint(4) unsigned DEFAULT '1' COMMENT '认证结果 1认证不通过 2认证通过',
    `be_idcard` float(4,4) unsigned DEFAULT '0.0000' COMMENT '相似度',
    `fail_reason` varchar(100) DEFAULT NULL COMMENT '认证失败原因',
    `id_card` varchar(18) DEFAULT NULL COMMENT '身份证号码',
    `id_name` varchar(25) DEFAULT NULL COMMENT '身份证姓名',
    `nation` varchar(25) DEFAULT NULL COMMENT '民族',
    `sex` tinyint(2) unsigned DEFAULT '1' COMMENT '性别 1男 2女',
    `birthday` date DEFAULT NULL COMMENT '生日',
    `age` tinyint(4) unsigned DEFAULT '0' COMMENT '年龄',
    `address` varchar(200) DEFAULT NULL COMMENT '身份证住址',
    `issuing_authority` varchar(200) DEFAULT NULL COMMENT '身份证签发机关',
    `idcard_start` date DEFAULT NULL COMMENT '身份证起始',
    `idcard_expired` date DEFAULT NULL COMMENT '身份证失效日期',
    `front_card` varchar(200) DEFAULT NULL COMMENT '身份证证件正面 照',
    `back_card` varchar(200) DEFAULT NULL COMMENT '身份证证件背面 照',
    `photo_get` varchar(200) DEFAULT NULL COMMENT '头像照',
    `photo_grid` varchar(200) DEFAULT NULL COMMENT '网格照',
    `photo_living` varchar(200) DEFAULT NULL COMMENT '活体清晰照',
    `info_order` varchar(255) DEFAULT NULL COMMENT '商户认证信息',
    `user_profiles` text COMMENT '通过云慧眼API获取用户档案',
    `user_report` text COMMENT '用户报告Y1001003接口',
    `create_time` datetime DEFAULT NULL,
    `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户活体检测记录表';

# 公用 地址选择器
CREATE TABLE `pf_areas` (
    `areaid` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) NOT NULL DEFAULT '',
    `joinname` varchar(150) NOT NULL DEFAULT '',
    `parentid` mediumint(8) unsigned NOT NULL DEFAULT '0',
    `vieworder` smallint(6) unsigned NOT NULL DEFAULT '0',
    PRIMARY KEY (`areaid`) USING BTREE,
    KEY `idx_name` (`name`) USING BTREE,
    KEY `idx_parentid_vieworder` (`parentid`,`vieworder`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=910007 DEFAULT CHARSET=utf8 COMMENT='地址选择器';

# 用户实名信息表
CREATE TABLE `pf_users_real` (
    `uid` int(11) unsigned NOT NULL COMMENT '用户UID',
    `full_name` varchar(255) NOT NULL COMMENT '姓名',
    `identity_number` char(18) NOT NULL COMMENT '身份证号码',
    `nationality` varchar(20) NOT NULL COMMENT '民族',
    `start_date` date NOT NULL COMMENT '起始日期',
    `end_date` date NOT NULL COMMENT '终止日期',
    `address` varchar(255) NOT NULL COMMENT '地址',
    `issuing_authority` varchar(255) NOT NULL COMMENT '颁发机关',
    `idcard_information_pic` varchar(255) NOT NULL COMMENT '身份证信息面照片',
    `idcard_national_pic` varchar(255) NOT NULL COMMENT '身份证国徽面照片',
    `face_recognition` varchar(10) NOT NULL COMMENT '是否通过⼈人脸识别,SUCCESS 通过，FAIL 未通过',
    `face_similarity` decimal(5,4) DEFAULT NULL COMMENT '人脸识别相似度',
    `face_fail_reason` varchar(255) DEFAULT NULL COMMENT '人脸识别失败原因',
    `face_living_pic` varchar(255) DEFAULT NULL COMMENT '人脸识别活体照片',
    `face_idcard_portrait_pic` varchar(255) DEFAULT NULL COMMENT '人脸识别身份证人像照片',
    `create_time` datetime DEFAULT NULL,
    `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户实名信息表';

# 用户联系信息表
CREATE TABLE `pf_users_contact` (
    `uid` int(11) unsigned NOT NULL COMMENT '用户UID',
    `email` varchar(255) DEFAULT NULL COMMENT '邮箱',
    `wechat` varchar(255) DEFAULT NULL COMMENT '微信名称',
    `qq` varchar(16) DEFAULT NULL COMMENT 'QQ',
    `home_province` int(10) unsigned DEFAULT NULL COMMENT '现居地址所在省',
    `home_city` int(10) unsigned DEFAULT NULL COMMENT '现居地址所在市或区',
    `home_area` int(10) unsigned DEFAULT NULL COMMENT '现居地址所在地域',
    `home_address` varchar(255) DEFAULT NULL COMMENT '现居地址详细信息',
    `housing_situation` varchar(50) DEFAULT '其他' COMMENT '住房情况：字典:宿舍、租房、与⽗母同住、与其他人同住、⾃有住房、其他',
    `marital_status` varchar(50) DEFAULT '其他' COMMENT '婚姻状况：字典:已婚有⼦女、已婚⽆⼦女、未婚、离异、其他',
    `contact_person` varchar(30) DEFAULT NULL COMMENT '紧急联系人',
    `contact_person_relation` varchar(30) DEFAULT NULL COMMENT '⽗母、配偶、监护⼈、⼦女',
    `contact_person_phone` char(11) DEFAULT NULL COMMENT '紧急联系人手机号',
    `create_time` datetime DEFAULT NULL,
    `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户联络信息表';

# 用户位置信息
CREATE TABLE `pf_users_location` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `uid` int(11) unsigned NOT NULL,
    `location` varchar(255) DEFAULT NULL COMMENT '坐标',
    `address` varchar(255) DEFAULT NULL COMMENT '坐标转地址描述',
    `channel` varchar(20) DEFAULT NULL COMMENT '获取坐标方法：GPS,IP',
    `ip_address` int(10) unsigned DEFAULT NULL COMMENT 'IP地址',
    `create_time` datetime DEFAULT NULL COMMENT '获取时间',
    PRIMARY KEY (`id`,`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户位置信息';

# 用户通讯录信息
CREATE TABLE `pf_users_phonebook` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增索引',
    `uid` int(11) unsigned NOT NULL COMMENT '用户UID',
    `phonebook_count` int(11) unsigned DEFAULT NULL COMMENT '条数',
    `phonebook` text COMMENT '通讯录详情',
    `phone_type` varchar(255) DEFAULT NULL COMMENT '设备类型，Android,IOS',
    `phone_id` varchar(255) DEFAULT NULL COMMENT '设备ID',
    `create_time` datetime DEFAULT NULL COMMENT '获取时间',
    PRIMARY KEY (`id`,`uid`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户通讯录';

# 用户职业信息
CREATE TABLE `pf_users_work` (
    `uid` int(11) unsigned NOT NULL COMMENT '用户UID',
    `highest_education` varchar(255) DEFAULT NULL COMMENT '最高学历：初中及以下、中专、高中、大专、本科、硕士、博士级以上',
    `profession` varchar(255) DEFAULT '其他' COMMENT '职业：其他、学生、公务员、事业单位员工、企业员工、私营业主、农民',
    `working_status` tinyint(2) unsigned DEFAULT NULL COMMENT ',1=在职，2=学生，3=待业',
    `monthly_income` decimal(10,0) unsigned DEFAULT NULL COMMENT '月收入，单位分',
    `edu_pic` varchar(255) DEFAULT NULL COMMENT '学历证明照片',
    `work_name` varchar(255) DEFAULT NULL COMMENT '单位名称',
    `work_province` int(10) unsigned DEFAULT NULL COMMENT '单位所在省',
    `work_city` int(10) unsigned DEFAULT NULL COMMENT '单位所在市或区',
    `work_area` int(10) unsigned DEFAULT NULL COMMENT '单位所在地域',
    `work_address` varchar(255) DEFAULT NULL COMMENT '单位详细地址',
    `work_entry _time` datetime DEFAULT NULL COMMENT '入职时间',
    `work_profession` varchar(255) DEFAULT NULL COMMENT '职位：高级管理人员、中层管理人员、基层管理人员、普通员工',
    `work_contact` varchar(13) DEFAULT NULL COMMENT '单位电话',
    `school_name` varchar(255) DEFAULT NULL COMMENT '学校名称',
    `school_province` int(10) unsigned DEFAULT NULL COMMENT '学校所在省',
    `school_city` int(10) unsigned DEFAULT NULL COMMENT '学校所在市或区',
    `school_area` int(10) unsigned DEFAULT NULL COMMENT '学校所在地域',
    `school_address` varchar(255) DEFAULT NULL COMMENT '学校详细地址',
    `school_contact` varchar(13) DEFAULT NULL COMMENT '学校电话',
    `school_major` varchar(255) DEFAULT NULL COMMENT '专业',
    `education_system` tinyint(4) unsigned DEFAULT NULL COMMENT '学制',
    `entrance_time` char(4) DEFAULT NULL COMMENT '入学年份',
    `train_contact` varchar(13) DEFAULT NULL COMMENT '机构联系方式',
    `create_time` datetime DEFAULT NULL,
    `update_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户学历与职业 ';

