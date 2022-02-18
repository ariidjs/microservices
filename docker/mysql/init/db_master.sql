CREATE DATABASE IF NOT EXISTS `db_driver`;
CREATE DATABASE IF NOT EXISTS `db_store`;
CREATE DATABASE IF NOT EXISTS `db_product`;
CREATE DATABASE IF NOT EXISTS `db_admin`;
CREATE DATABASE IF NOT EXISTS `db_category`;
CREATE DATABASE IF NOT EXISTS `db_detail_transaction`;
CREATE DATABASE IF NOT EXISTS `db_saldo_store`;
CREATE DATABASE IF NOT EXISTS `db_saldo_driver`;
CREATE DATABASE IF NOT EXISTS `db_transaction`;
CREATE DATABASE IF NOT EXISTS `db_customer`;
CREATE DATABASE IF NOT EXISTS `db_promo`;
CREATE DATABASE IF NOT EXISTS `db_management`;
CREATE DATABASE IF NOT EXISTS `db_benefit`;
CREATE DATABASE IF NOT EXISTS `db_rating`;


GRANT ALL ON *.* TO 'user'@'%';