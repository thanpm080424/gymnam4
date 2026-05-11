-- Migration: Add hinh_anh column to THONG_BAO_HE_THONG
-- Run this once to add image support to announcements

ALTER TABLE THONG_BAO_HE_THONG
ADD COLUMN hinh_anh VARCHAR(500) DEFAULT NULL AFTER noi_dung;
