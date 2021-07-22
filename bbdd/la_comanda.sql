-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-07-2021 a las 19:20:14
-- Versión del servidor: 10.4.19-MariaDB
-- Versión de PHP: 8.0.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `la_comanda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas`
--

CREATE TABLE `encuestas` (
  `id` int(11) NOT NULL,
  `codigo_pedido` varchar(5) NOT NULL COMMENT 'codigo del pedido',
  `mesa` int(2) NOT NULL COMMENT 'Puntuacion 1 a 10',
  `restaurante` int(2) NOT NULL COMMENT 'Puntuacion 1 a 10',
  `mozo` int(2) NOT NULL COMMENT 'Puntuacion 1 a 10',
  `cocina` int(2) NOT NULL COMMENT 'Puntuacion 1 a 10',
  `resumen` varchar(66) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadomesas`
--

CREATE TABLE `estadomesas` (
  `id` int(11) NOT NULL,
  `estado` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `estadomesas`
--

INSERT INTO `estadomesas` (`id`, `estado`) VALUES
(1, 'con cliente esperando pedido'),
(2, 'con cliente comiendo'),
(3, 'con cliente pagando'),
(4, 'cerrada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadopedidos`
--

CREATE TABLE `estadopedidos` (
  `id` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `estadopedidos`
--

INSERT INTO `estadopedidos` (`id`, `estado`) VALUES
(1, 'pendiente'),
(2, 'en preparacion'),
(3, 'listo para servir'),
(4, 'entregado'),
(5, 'abonado'),
(6, 'cancelado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadousuarios`
--

CREATE TABLE `estadousuarios` (
  `id` int(11) NOT NULL,
  `estado` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `estadousuarios`
--

INSERT INTO `estadousuarios` (`id`, `estado`) VALUES
(1, 'activo'),
(2, 'suspendido');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historialmesas`
--

CREATE TABLE `historialmesas` (
  `id` int(11) NOT NULL,
  `id_mesa` int(11) NOT NULL COMMENT 'id de la mesa',
  `id_estado` int(11) NOT NULL,
  `id_estado_new` int(11) NOT NULL,
  `fecha_cambio` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historialpedidos`
--

CREATE TABLE `historialpedidos` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `id_estado_new` int(11) NOT NULL,
  `fecha_cambio` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historialusuarios`
--

CREATE TABLE `historialusuarios` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_estado` int(11) NOT NULL,
  `id_estado_new` int(11) NOT NULL,
  `fecha_cambio` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lista_productos_empleados`
--

CREATE TABLE `lista_productos_empleados` (
  `id` int(11) NOT NULL,
  `id_empleado` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto_pedido` int(11) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `codigo` varchar(5) COLLATE utf8_unicode_ci NOT NULL,
  `sector` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `id_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `codigo`, `sector`, `id_estado`) VALUES
(1, 'ehH26', 'Sur', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `codigo` varchar(5) NOT NULL COMMENT 'código de pedido (único)',
  `codigo_mesa` varchar(5) NOT NULL COMMENT 'codigo mesa 5 caracteres',
  `id_cliente` int(11) NOT NULL,
  `id_mozo` int(11) NOT NULL,
  `lista` varchar(256) NOT NULL COMMENT 'JSON de id_producto''s',
  `estado` int(11) NOT NULL,
  `pedido_time` datetime NOT NULL DEFAULT current_timestamp(),
  `eta` time DEFAULT NULL,
  `entrega_time` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `codigo`, `codigo_mesa`, `id_cliente`, `id_mozo`, `lista`, `estado`, `pedido_time`, `eta`, `entrega_time`) VALUES
(1, '4ugBc', 'ehH26', 5, 2, '{\"id\":1,\"nombre\":\"Coca Cola\",\"tipo\":\"bebida\"}', 1, '2021-07-20 16:18:25', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfilesusuarios`
--

CREATE TABLE `perfilesusuarios` (
  `id` int(11) NOT NULL,
  `perfil` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `perfilesusuarios`
--

INSERT INTO `perfilesusuarios` (`id`, `perfil`) VALUES
(1, 'cliente'),
(2, 'bartender'),
(3, 'cervecero'),
(4, 'cocinero'),
(5, 'mozo'),
(6, 'socio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipoproductos`
--

CREATE TABLE `tipoproductos` (
  `id` int(11) NOT NULL,
  `tipo` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `tipoproductos`
--

INSERT INTO `tipoproductos` (`id`, `tipo`) VALUES
(1, 'bar'),
(2, 'cerveza'),
(3, 'cocina');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `clave` varchar(30) NOT NULL,
  `sector` varchar(30) DEFAULT NULL,
  `tipo` varchar(30) NOT NULL,
  `estado_id` int(11) NOT NULL,
  `alta` datetime NOT NULL,
  `baja` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `clave`, `sector`, `tipo`, `estado_id`, `alta`, `baja`) VALUES
(6, 'Cristian', '123456', NULL, '6', 1, '2021-06-21 00:00:00', NULL),
(7, 'Esteban', '123456', NULL, '5', 1, '2021-06-21 00:00:00', NULL),
(8, 'Ricardo', '123456', 'cocina', '4', 1, '2021-06-21 00:00:00', NULL),
(9, 'Fausto', '123456', NULL, '6', 1, '2021-06-21 00:00:00', NULL),
(15, 'Tomas', '123456', 'bar', '2', 1, '2021-06-22 00:00:00', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_pedido` (`codigo_pedido`);

--
-- Indices de la tabla `estadomesas`
--
ALTER TABLE `estadomesas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estadopedidos`
--
ALTER TABLE `estadopedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estadousuarios`
--
ALTER TABLE `estadousuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historialmesas`
--
ALTER TABLE `historialmesas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historialpedidos`
--
ALTER TABLE `historialpedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `historialusuarios`
--
ALTER TABLE `historialusuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `lista_productos_empleados`
--
ALTER TABLE `lista_productos_empleados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `perfilesusuarios`
--
ALTER TABLE `perfilesusuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tipoproductos`
--
ALTER TABLE `tipoproductos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `encuestas`
--
ALTER TABLE `encuestas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estadomesas`
--
ALTER TABLE `estadomesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estadopedidos`
--
ALTER TABLE `estadopedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `estadousuarios`
--
ALTER TABLE `estadousuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `historialmesas`
--
ALTER TABLE `historialmesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historialpedidos`
--
ALTER TABLE `historialpedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historialusuarios`
--
ALTER TABLE `historialusuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `lista_productos_empleados`
--
ALTER TABLE `lista_productos_empleados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `perfilesusuarios`
--
ALTER TABLE `perfilesusuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tipoproductos`
--
ALTER TABLE `tipoproductos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
