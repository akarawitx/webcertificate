<?php
// backend/lib/error_handler.php
// ฟังก์ชันกลางสำหรับตอบกลับ error แบบปลอดภัย:
// - log รายละเอียดจริงทั้งหมดไว้ฝั่ง server (ผ่าน error_log ไปโผล่ที่ Render Logs)
// - ส่งข้อความทั่วไปกลับไปหา client เท่านั้น ไม่เปิดเผยรายละเอียดภายใน เช่น
//   ชื่อ table, ชื่อ column, connection string ที่อาจช่วยผู้ไม่หวังดีโจมตีระบบได้ง่ายขึ้น
//
// วิธีใช้:
//   try {
//       ... โค้ดที่อาจ throw exception ...
//   } catch (Throwable $e) {
//       send_error_response($e, 'ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง');
//   }

function send_error_response(Throwable $e, string $publicMessage = 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง', int $httpCode = 500): void
{
    // log รายละเอียดจริงทั้งหมดไว้ฝั่ง server เท่านั้น (เห็นได้ผ่าน Render -> Logs)
    error_log('[error] ' . $e->getMessage() . ' | file: ' . $e->getFile() . ':' . $e->getLine());

    http_response_code($httpCode);
    echo json_encode(['ok' => false, 'error' => $publicMessage], JSON_UNESCAPED_UNICODE);
}