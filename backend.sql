/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 80030 (8.0.30)
 Source Host           : localhost:3306
 Source Schema         : backend

 Target Server Type    : MySQL
 Target Server Version : 80030 (8.0.30)
 File Encoding         : 65001

 Date: 28/01/2025 11:20:56
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for usuarios_accesos
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_accesos`;
CREATE TABLE `usuarios_accesos`  (
  `idAcceso` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `idUsuario` int UNSIGNED NOT NULL,
  `DateTime` datetime NOT NULL,
  `token` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `expiration_date` datetime NOT NULL,
  PRIMARY KEY (`idAcceso`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci COMMENT = 'Limpiar al entregar' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios_accesos
-- ----------------------------
INSERT INTO `usuarios_accesos` VALUES (1, 1, '2025-01-20 14:11:33', 'asdqwe123', '2025-01-29 14:11:51');

-- ----------------------------
-- Table structure for usuarios_listado
-- ----------------------------
DROP TABLE IF EXISTS `usuarios_listado`;
CREATE TABLE `usuarios_listado`  (
  `idUsuario` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `idEstado` int UNSIGNED NULL DEFAULT NULL,
  `Password` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `email` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Nombre` varchar(60) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Rut` varchar(13) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  `Direccion_img` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT NULL,
  PRIMARY KEY (`idUsuario`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci COMMENT = 'Cuidado' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of usuarios_listado
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
