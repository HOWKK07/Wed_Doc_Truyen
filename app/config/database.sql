-- Tạo bảng users nếu chưa tồn tại
CREATE TABLE IF NOT EXISTS `users` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `username` varchar(50) NOT NULL,
    `password` varchar(255) NOT NULL,
    `email` varchar(100) NOT NULL,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `username` (`username`),
    UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tạo bảng truyen nếu chưa tồn tại
CREATE TABLE IF NOT EXISTS `truyen` (
    `id_truyen` int(11) NOT NULL AUTO_INCREMENT,
    `ten_truyen` varchar(255) NOT NULL,
    `mo_ta` text,
    `anh_bia` varchar(255) NOT NULL,
    `luot_xem` int(11) DEFAULT '0',
    `ngay_dang` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_truyen`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tạo bảng chuong nếu chưa tồn tại
CREATE TABLE IF NOT EXISTS `chuong` (
    `id_chuong` int(11) NOT NULL AUTO_INCREMENT,
    `id_truyen` int(11) NOT NULL,
    `so_chuong` int(11) NOT NULL,
    `ten_chuong` varchar(255) NOT NULL,
    `noi_dung` text NOT NULL,
    `ngay_cap_nhat` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_chuong`),
    KEY `id_truyen` (`id_truyen`),
    CONSTRAINT `chuong_ibfk_1` FOREIGN KEY (`id_truyen`) REFERENCES `truyen` (`id_truyen`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tạo bảng lich_su_doc
CREATE TABLE IF NOT EXISTS `lich_su_doc` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `id_user` int(11) NOT NULL,
    `id_truyen` int(11) NOT NULL,
    `chuong_doc` int(11) NOT NULL,
    `ngay_doc` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `id_user` (`id_user`),
    KEY `id_truyen` (`id_truyen`),
    CONSTRAINT `lich_su_doc_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
    CONSTRAINT `lich_su_doc_ibfk_2` FOREIGN KEY (`id_truyen`) REFERENCES `truyen` (`id_truyen`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 