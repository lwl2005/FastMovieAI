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

 Date: 15/05/2024 18:50:00
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for php_plugin_article
-- ----------------------------
DROP TABLE IF EXISTS `php_plugin_article`;
CREATE TABLE `php_plugin_article`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_time` datetime NULL DEFAULT NULL,
  `update_time` datetime NULL DEFAULT NULL,
  `release_time` datetime NULL DEFAULT NULL,
  `alias` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文章别名',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '文章标题',
  `subtitle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `state` tinyint(1) NULL DEFAULT 1,
  `examine` tinyint(1) NULL DEFAULT NULL,
  `view` int NULL DEFAULT 0,
  `thumb` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '文章封面',
  `class` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `source` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `description` varchar(400) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `classify_id` int NULL DEFAULT NULL,
  `sort` tinyint NULL DEFAULT 99,
  `delete_time` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `alias`(`alias` ASC) USING BTREE,
  INDEX `cid`(`classify_id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for php_plugin_article_classify
-- ----------------------------
DROP TABLE IF EXISTS `php_plugin_article_classify`;
CREATE TABLE `php_plugin_article_classify`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `create_time` datetime NULL DEFAULT NULL COMMENT '分类创建时间',
  `update_time` datetime NULL DEFAULT NULL,
  `title` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '分类名称',
  `alias` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '别名',
  `pid` int NULL DEFAULT NULL COMMENT '父级ID',
  `sort` int NULL DEFAULT 99 COMMENT '排序',
  `state` tinyint(1) NULL DEFAULT 1,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `alias`(`alias` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章分类数据' ROW_FORMAT = COMPACT;

-- ----------------------------
-- Table structure for php_plugin_article_content
-- ----------------------------
DROP TABLE IF EXISTS `php_plugin_article_content`;
CREATE TABLE `php_plugin_article_content`  (
  `article_id` int UNSIGNED NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  PRIMARY KEY (`article_id`) USING BTREE,
  UNIQUE INDEX `article`(`article_id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '文章内容表' ROW_FORMAT = DYNAMIC;

SET FOREIGN_KEY_CHECKS = 1;
