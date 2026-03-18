-- Roles del sistema
INSERT INTO roles (role_name, description) VALUES 
('Admin', 'Acceso total al sistema'),
('Dentist', 'Gestión clínica e historial de pacientes'),
('Warehouse Manager', 'Control de inventario y stock'),
('Receptionist', 'Gestión de citas y ventas');

-- Estados de las citas
INSERT INTO appointment_statuses (status_name, description) VALUES 
('Scheduled', 'Cita agendada pero aún no atendida'),
('Attended', 'El paciente fue atendido y se registró su historial'),
('Cancelled', 'Cita cancelada por el paciente o la clínica'),
('No-Show', 'El paciente no se presentó a la cita');

-- Estados de las ventas
INSERT INTO sale_statuses (status_name, description) VALUES 
('Paid', 'Transacción completada con éxito'),
('Pending', 'Factura generada pero aún no pagada'),
('Refunded', 'Pago devuelto al cliente');

-- Tipos de movimientos de inventario
INSERT INTO movement_types (type_name, description) VALUES 
('Purchase', 'Nuevo stock recibido de proveedor'),
('Sale', 'Stock reducido por compra de cliente'),
('Adjustment - Loss', 'Reducción manual por daño o robo'),
('Adjustment - Expiry', 'Reducción manual por producto vencido'),
('Internal Use', 'Materiales utilizados durante procedimientos clínicos');


-- Usuarios de prueba

-- Administrador (Admin)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (1, 'admin1', 'admin1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88881111', TRUE);

-- Dentista (Dentist)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (2, 'dentist1', 'dentist1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88882222', TRUE);

-- Bodeguero (Warehouse Manager)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (3, 'warehouse1', 'warehouse1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88883333', TRUE);

-- Recepcionista (Receptionist)
INSERT INTO users (id_role, full_name, email, password_hash, phone, active)
VALUES (4, 'receptionist1', 'receptionist1@blanccare.com', '$2y$10$b1xCdNAqUTR2KmIvTvvOr.mdR0KLdX.Ykzo/fHDB28bdv8r34Z6FK', '88884444', TRUE);

-- categorías de productos
INSERT INTO product_categories (category_name, description) VALUES 
('Materiales de protección', 'Materiales de protección'),
('Materiales de tratamiento dental', 'Materiales de tratamiento dental'),
('Materiales de anestesia', 'Materiales de anestesia'),
('Instrumental desechable', 'Instrumental desechable'),
('Materiales de limpieza y esterilización', 'Materiales de limpieza y esterilización'),
('Materiales de impresión dental', 'Materiales de impresión dental'),
('Otros insumos comunes', 'Otros insumos comunes'),
('Productos de higiene diaria', 'Productos de higiene diaria'),
('Productos para ortodoncia', 'Productos para ortodoncia'),
('Productos post-tratamiento', 'Productos post-tratamiento'),
('Productos para blanqueamiento', 'Productos para blanqueamiento'),
('Productos para prótesis dentales', 'Productos para prótesis dentales');

-- Productos

-- Materiales de protección (id_category = 1)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(1, 'Guantes desechables', 0, 0, 5, 'Unidad'),
(1, 'Mascarillas quirúrgicas', 0, 0, 5, 'Unidad'),
(1, 'Caretas de protección', 0, 0, 5, 'Unidad'),
(1, 'Gorros desechables', 0, 0, 5, 'Unidad'),
(1, 'Batas desechables', 0, 0, 5, 'Unidad');

-- Materiales de tratamiento dental (id_category = 2)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(2, 'Resina dental', 0, 0, 5, 'Unidad'),
(2, 'Amalgama dental', 0, 0, 5, 'Unidad'),
(2, 'Cemento dental', 0, 0, 5, 'Unidad'),
(2, 'Ácido grabador', 0, 0, 5, 'Unidad'),
(2, 'Adhesivo dental', 0, 0, 5, 'Unidad'),
(2, 'Ionómero de vidrio', 0, 0, 5, 'Unidad');

-- Materiales de anestesia (id_category = 3)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(3, 'Anestesia local', 0, 0, 5, 'Unidad'),
(3, 'Agujas para anestesia', 0, 0, 5, 'Unidad'),
(3, 'Carpules de anestesia', 0, 0, 5, 'Unidad');

-- Instrumental desechable (id_category = 4)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(4, 'Jeringas desechables', 0, 0, 5, 'Unidad'),
(4, 'Vasos desechables', 0, 0, 5, 'Unidad'),
(4, 'Baberos dentales', 0, 0, 5, 'Unidad'),
(4, 'Gasas estériles', 0, 0, 5, 'Unidad'),
(4, 'Algodón dental', 0, 0, 5, 'Unidad');

-- Materiales de limpieza y esterilización (id_category = 5)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(5, 'Alcohol', 0, 0, 5, 'Unidad'),
(5, 'Hipoclorito (cloro)', 0, 0, 5, 'Unidad'),
(5, 'Desinfectante de superficies', 0, 0, 5, 'Unidad'),
(5, 'Bolsas de esterilización', 0, 0, 5, 'Unidad'),
(5, 'Indicadores de esterilización', 0, 0, 5, 'Unidad');

-- Materiales de impresión dental (id_category = 6)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(6, 'Alginato', 0, 0, 5, 'Unidad'),
(6, 'Silicona de impresión', 0, 0, 5, 'Unidad'),
(6, 'Cubetas de impresión', 0, 0, 5, 'Unidad');

-- Otros insumos comunes (id_category = 7)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(7, 'Hilo dental', 0, 0, 5, 'Unidad'),
(7, 'Pasta profiláctica', 0, 0, 5, 'Unidad'),
(7, 'Cepillos dentales', 0, 0, 5, 'Unidad'),
(7, 'Fluoruro', 0, 0, 5, 'Unidad'),
(7, 'Rollos de algodón', 0, 0, 5, 'Unidad');

-- Productos de higiene diaria (id_category = 8) - Nota: algunos se repiten de la categoría anterior
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(8, 'Cepillo dental', 0, 0, 5, 'Unidad'),
(8, 'Cepillo dental eléctrico', 0, 0, 5, 'Unidad'),
(8, 'Hilo dental', 0, 0, 5, 'Unidad'),
(8, 'Pasta dental', 0, 0, 5, 'Unidad'),
(8, 'Enjuague bucal', 0, 0, 5, 'Unidad'),
(8, 'Cepillos interdentales', 0, 0, 5, 'Unidad'),
(8, 'Limpiador de lengua', 0, 0, 5, 'Unidad');

-- Productos para ortodoncia (id_category = 9)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(9, 'Cera para brackets', 0, 0, 5, 'Unidad'),
(9, 'Cepillo especial para ortodoncia', 0, 0, 5, 'Unidad'),
(9, 'Hilo dental para brackets', 0, 0, 5, 'Unidad'),
(9, 'Enjuague bucal especial para ortodoncia', 0, 0, 5, 'Unidad');

-- Productos post-tratamiento (id_category = 10)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(10, 'Gel desensibilizante dental', 0, 0, 5, 'Unidad'),
(10, 'Enjuague bucal con clorhexidina', 0, 0, 5, 'Unidad'),
(10, 'Pasta dental para dientes sensibles', 0, 0, 5, 'Unidad'),
(10, 'Gel de fluoruro', 0, 0, 5, 'Unidad'),
(10, 'Analgésico recomendado por el odontólogo (si la clínica lo maneja)', 0, 0, 5, 'Unidad');

-- Productos para blanqueamiento (id_category = 11)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(11, 'Kit de blanqueamiento dental', 0, 0, 5, 'Unidad'),
(11, 'Gel blanqueador', 0, 0, 5, 'Unidad'),
(11, 'Jeringas de blanqueamiento', 0, 0, 5, 'Unidad');

-- Productos para prótesis dentales (id_category = 12)
INSERT INTO products (id_category, product_name, sale_price, purchase_price, min_stock, measurement_unit) VALUES
(12, 'Adhesivo para dentaduras', 0, 0, 5, 'Unidad'),
(12, 'Pastillas limpiadoras de prótesis', 0, 0, 5, 'Unidad'),
(12, 'Cepillo para prótesis dentales', 0, 0, 5, 'Unidad');