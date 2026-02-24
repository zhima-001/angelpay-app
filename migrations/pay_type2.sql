/*
 Navicat Premium Data Transfer

 Source Server         : cicd天使
 Source Server Type    : MySQL
 Source Server Version : 50744
 Source Host           : 45.32.105.89:20010
 Source Schema         : tianshi

 Target Server Type    : MySQL
 Target Server Version : 50744
 File Encoding         : 65001

 Date: 11/08/2025 17:21:35
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for pay_type
-- ----------------------------
DROP TABLE IF EXISTS `pay_type`;
CREATE TABLE `pay_type`  (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `device` int(1) UNSIGNED NOT NULL DEFAULT 0,
  `showname` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '支付方式' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of pay_type
-- ----------------------------
INSERT INTO `pay_type` VALUES (1, 'alipay', 0, '支付宝', 1);
INSERT INTO `pay_type` VALUES (2, 'wxpay', 0, '微信支付', 1);
INSERT INTO `pay_type` VALUES (3, 'qqpay', 0, 'QQ钱包', 1);
INSERT INTO `pay_type` VALUES (4, 'webbank', 0, '网银支付', 1);
INSERT INTO `pay_type` VALUES (5, 'yunshanpay', 0, '云闪付', 1);
INSERT INTO `pay_type` VALUES (6, 'kaka', 0, '卡转卡--test', 1);
INSERT INTO `pay_type` VALUES (7, 'shuzi', 0, '数字货币', 1);

SET FOREIGN_KEY_CHECKS = 1;
