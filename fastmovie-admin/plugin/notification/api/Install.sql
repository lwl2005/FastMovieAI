/*
 Navicat Premium Data Transfer

 Source Server         : Loong
 Source Server Type    : MySQL
 Source Server Version : 80024 (8.0.24)
 Source Host           : 127.0.0.1:3306
 Source Schema         : cloud_xhadmin_cn

 Target Server Type    : MySQL
 Target Server Version : 80024 (8.0.24)
 File Encoding         : 65001

 Date: 18/05/2024 18:25:14
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for php_plugin_notification_log
-- ----------------------------
DROP TABLE IF EXISTS `php_plugin_notification_log`;
CREATE TABLE `php_plugin_notification_log`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int NULL DEFAULT NULL,
  `admin_uid` int NULL DEFAULT NULL,
  `scene` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `num` int NULL DEFAULT NULL,
  `create_time` datetime NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
