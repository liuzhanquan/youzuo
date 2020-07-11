/*
 Navicat Premium Data Transfer

 Source Server         : 192.168.1.220
 Source Server Type    : MySQL
 Source Server Version : 80019
 Source Host           : 192.168.1.220:3306
 Source Schema         : codecheck

 Target Server Type    : MySQL
 Target Server Version : 80019
 File Encoding         : 65001

 Date: 11/07/2020 00:58:29
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for bear_admin
-- ----------------------------
DROP TABLE IF EXISTS `bear_admin`;
CREATE TABLE `bear_admin`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `password` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `role_id` int NULL DEFAULT NULL COMMENT 'Ȩ',
  `reg_time` datetime(0) NULL DEFAULT NULL COMMENT 'ע',
  `authkey` char(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `status` tinyint NULL DEFAULT 1 COMMENT '״̬',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_admin_log
-- ----------------------------
DROP TABLE IF EXISTS `bear_admin_log`;
CREATE TABLE `bear_admin_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NULL DEFAULT NULL COMMENT '管理员ID',
  `desc` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作说明',
  `action` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作方法',
  `timestamp` datetime(0) NULL DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 73 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员登陆日志' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_category
-- ----------------------------
DROP TABLE IF EXISTS `bear_category`;
CREATE TABLE `bear_category`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int NULL DEFAULT NULL,
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `image` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `sort` int NULL DEFAULT NULL,
  `type` tinyint(1) NULL DEFAULT 1 COMMENT '1',
  `level` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 28 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_config
-- ----------------------------
DROP TABLE IF EXISTS `bear_config`;
CREATE TABLE `bear_config`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `name` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '参数名称',
  `value` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '参数对应值',
  `remark` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '参数说明',
  `is_radio` tinyint(1) NULL DEFAULT 0,
  `is_must` tinyint(1) NULL DEFAULT 0,
  `is_sys` tinyint(1) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '全局参数配置' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_cus_category
-- ----------------------------
DROP TABLE IF EXISTS `bear_cus_category`;
CREATE TABLE `bear_cus_category`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int NULL DEFAULT NULL,
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `image` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `sort` int NULL DEFAULT NULL,
  `type` tinyint(1) NULL DEFAULT 1 COMMENT '1',
  `level` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 23 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_customer
-- ----------------------------
DROP TABLE IF EXISTS `bear_customer`;
CREATE TABLE `bear_customer`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `cid` int NULL DEFAULT NULL,
  `customer_sn` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '业务员编号',
  `customer_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '业务员名称',
  `parent_id` int NULL DEFAULT NULL,
  `phone` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号码',
  `money` decimal(10, 2) NULL DEFAULT 0.00,
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '是否显示 1显示 2隐藏',
  `sort` int NULL DEFAULT NULL COMMENT '排序',
  `timestamp` datetime(0) NULL DEFAULT NULL,
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_detection
-- ----------------------------
DROP TABLE IF EXISTS `bear_detection`;
CREATE TABLE `bear_detection`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `goods_id` int NULL DEFAULT NULL COMMENT '产品id',
  `customer_id` int NULL DEFAULT NULL COMMENT '业务员id',
  `start_num` int NULL DEFAULT 0 COMMENT '二维码起始号',
  `end_num` int NULL DEFAULT 0 COMMENT '二维码结束号',
  `count_num` int NULL DEFAULT 0 COMMENT '配置数量',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '1启用2冻结',
  `sort` int NULL DEFAULT 0 COMMENT '排序',
  `timestamp` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `log_status` enum('1','2') CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '2' COMMENT '是否生成记录 1 生成记录 2未生成记录',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `start_num,end_num`(`start_num`, `end_num`) USING BTREE COMMENT '起始、结束编号',
  INDEX `goods_id`(`goods_id`) USING BTREE COMMENT '产品id',
  INDEX `customer_id`(`customer_id`) USING BTREE COMMENT '业务员id'
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_goods
-- ----------------------------
DROP TABLE IF EXISTS `bear_goods`;
CREATE TABLE `bear_goods`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `cid` int NULL DEFAULT NULL,
  `title` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '产品名',
  `good_sn` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '产品编号',
  `price` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '产品价格',
  `image` char(150) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '产品图片',
  `photo` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '产品图片【轮播图】',
  `content` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `status` tinyint UNSIGNED NULL DEFAULT 1 COMMENT '是否显示 1 显示 2 隐藏',
  `sort` int UNSIGNED NULL DEFAULT 0 COMMENT '排序',
  `timestamp` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '产品表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_group
-- ----------------------------
DROP TABLE IF EXISTS `bear_group`;
CREATE TABLE `bear_group`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `menu_power` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '菜单权限',
  `power` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '功能权限',
  `is_sys` tinyint(1) NULL DEFAULT 0 COMMENT '是否为超级管理员',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_menu
-- ----------------------------
DROP TABLE IF EXISTS `bear_menu`;
CREATE TABLE `bear_menu`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `parent_id` int NULL DEFAULT NULL COMMENT '上级ID，0为顶级栏目',
  `name` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '栏目名称',
  `model` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '栏目模型',
  `action` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '栏目执行方法',
  `extend` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '继承上级方法',
  `sort` int NULL DEFAULT NULL COMMENT '排序',
  `icon` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '图标',
  `is_show` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 59 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理后台操作栏目' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_order
-- ----------------------------
DROP TABLE IF EXISTS `bear_order`;
CREATE TABLE `bear_order`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_sn` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '防伪码',
  `customer_id` int NOT NULL COMMENT '客户id',
  `goods_id` int NOT NULL COMMENT '产品id',
  `staff_id` int NOT NULL COMMENT '核销员id',
  `detection_id` int NULL DEFAULT NULL COMMENT '分配记录id',
  `code` int NOT NULL COMMENT '流水号',
  `status` tinyint NULL DEFAULT 0 COMMENT '状态 1 显示 2 隐藏',
  `remark` varchar(300) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '备注',
  `create_time` datetime(0) NULL DEFAULT NULL COMMENT '添加时间',
  `update_time` datetime(0) NULL DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `订单号`(`order_sn`) USING BTREE,
  INDEX `customer_id`(`customer_id`) USING BTREE,
  INDEX `goods_id`(`goods_id`) USING BTREE,
  INDEX `staff_id`(`staff_id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 13 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '检测单表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_region
-- ----------------------------
DROP TABLE IF EXISTS `bear_region`;
CREATE TABLE `bear_region`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `pid` int NULL DEFAULT NULL COMMENT '父id',
  `shortname` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '简称',
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '名称',
  `merger_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '全称',
  `level` tinyint UNSIGNED NULL DEFAULT 0 COMMENT '层级 1 2 3 省市区县',
  `pinyin` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '拼音',
  `code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '长途区号',
  `zip_code` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮编',
  `first` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '首字母',
  `lng` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '经度',
  `lat` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '纬度',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3749 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_sms_log
-- ----------------------------
DROP TABLE IF EXISTS `bear_sms_log`;
CREATE TABLE `bear_sms_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `phone` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `content` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_sms_tpl
-- ----------------------------
DROP TABLE IF EXISTS `bear_sms_tpl`;
CREATE TABLE `bear_sms_tpl`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `code` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `call_index` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `content` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 65 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_staff
-- ----------------------------
DROP TABLE IF EXISTS `bear_staff`;
CREATE TABLE `bear_staff`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `cid` int NULL DEFAULT NULL,
  `staff_sn` char(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '员工编号',
  `parent_id` int NULL DEFAULT NULL,
  `phone` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '登录账号',
  `password` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '密码，加密',
  `password_show` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '密码，未加密',
  `photo` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '员工头像',
  `status` tinyint(1) NULL DEFAULT 0 COMMENT '0待审核1已启用2已冻结',
  `email` char(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '邮箱',
  `timestamp` datetime(0) NULL DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '员工名称',
  `content` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `power` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '权限',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '核销人员列表' ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for bear_staff_log
-- ----------------------------
DROP TABLE IF EXISTS `bear_staff_log`;
CREATE TABLE `bear_staff_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int NULL DEFAULT NULL COMMENT '管理员ID',
  `desc` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作说明',
  `action` varchar(200) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '操作方法',
  `timestamp` datetime(0) NULL DEFAULT NULL COMMENT '操作时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '管理员登陆日志' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
