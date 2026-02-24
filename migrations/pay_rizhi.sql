/*
 Navicat Premium Data Transfer

 Source Server         : 所有cicd测试站的数据库
 Source Server Type    : MySQL
 Source Server Version : 50744
 Source Host           : 207.244.237.81:33306
 Source Schema         : tianshi

 Target Server Type    : MySQL
 Target Server Version : 50744
 File Encoding         : 65001

 Date: 07/11/2025 12:27:14
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for pay_rizhi
-- ----------------------------
DROP TABLE IF EXISTS `pay_rizhi`;
CREATE TABLE `pay_rizhi`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NULL DEFAULT NULL COMMENT '商户id',
  `ddh` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '订单号',
  `henji` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '痕迹',
  `getinfo` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL COMMENT '接收信息',
  `ip` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `addtime` int(11) NULL DEFAULT NULL COMMENT '添加时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 6 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
