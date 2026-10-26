# ระบบบริจาคออนไลน์ e-Donation โรงพยาบาลเชียงแสน (Google Sheet Database)

เว็บเพจรับบริจาค e-Donation โรงพยาบาลเชียงแสน เชื่อมต่อฐานข้อมูล **Google Sheets** โดยอัตโนมัติ ออกแบบตามต้นแบบ `chiangsaenweb\resources\views\donation.blade.php`

---

## 🌟 ฟีเจอร์เด่น
1. **Google Sheets Database**: ทุกข้อมูลการบริจาคและขอใบอนุโมทนาบัตรจะถูกบันทึกลง Google Sheet แบบเรียลไทม์
2. **อัปโหลดภาพลง Google Drive**: รูปถ่ายและภาพสลิปโอนเงินจะถูกบันทึกลงใน Google Drive และสร้างลิงก์คลิกเปิดดูได้โดยตรงใน Google Sheet
3. **ระบบสำรองข้อมูลในตัว (Local Backup)**: เก็บสำรองข้อมูลไว้ใน `data/donations_backup.json` และไฟล์รูปใน `uploads/` ป้องกันข้อมูลสูญหายกรณีเน็ตหลุด
4. **กระดานข้อความความปรารถนาดี (Wall of Kindness)**: ดึงข้อความให้กำลังใจจากผู้บริจาคที่อนุญาตให้เผยแพร่มาแสดงผลบนหน้าเว็บ
5. **รองรับ Responsive เต็มรูปแบบ**: ปรับการแสดงผลสวยงามบนมือถือ แท็บเล็ต และคอมพิวเตอร์
6. **หน้าต่างตั้งค่า Admin ในตัว**: สามารถใส่ URL ของ Google Apps Script และกดทดสอบการเชื่อมต่อได้ทันทีผ่านหน้าเว็บ

---

## 📋 ขั้นตอนการเชื่อมต่อกับ Google Sheet (ทำเพียงครั้งเดียว 3 นาที)

### ขั้นตอนที่ 1: เตรียม Google Sheet
1. เปิดเว็บไซต์ [Google Sheets](https://sheets.google.com) แล้วสร้างสเปรดชีตใหม่ (เช่น ตั้งชื่อว่า `ข้อมูลบริจาค รพ.เชียงแสน`)
2. ไปที่แถบเมนูด้านบน เลือก **ส่วนขยาย (Extensions)** > **Apps Script**

### ขั้นตอนที่ 2: วางโค้ด Google Apps Script
1. ลบโค้ดเริ่มต้นในหน้าต่าง Apps Script ออกให้หมด
2. เปิดไฟล์ `google-apps-script/Code.gs` ในโฟลเดอร์นี้ แล้วคัดลอกโค้ดทั้งหมดไปวาง
3. กดปุ่ม **บันทึก (Save)** รูปแผ่นดิสก์

### ขั้นตอนที่ 3: ทำให้ใช้งานได้ (Deploy เป็น Web App)
1. กดปุ่มสีน้ำเงิน **ทำให้ใช้งานได้ (Deploy)** ที่มุมขวาบน > เลือก **การทำให้ใช้งานได้ใหม่ (New deployment)**
2. คลิกไอคอนรูปเฟือง ⚙️ ด้านซ้าย > เลือก **เว็บแอป (Web app)**
3. ตั้งค่าดังนี้:
   - **คำอธิบาย (Description)**: `CSH Donation Web App`
   - **ดำเนินการในฐานะ (Execute as)**: `ตัวฉัน (Me)`
   - **ผู้ที่มีสิทธิ์เข้าถึง (Who has access)**: **`ทุกคน (Anyone)`**  *(⚠️ สำคัญมาก ต้องเลือก Anyone เพื่อให้หน้าเว็บส่งข้อมูลเข้าได้)*
4. กดปุ่ม **ทำให้ใช้งานได้ (Deploy)**
5. Google จะขอให้อนุญาตสิทธิ์ (Authorize access) ให้กดเลือกบัญชี Google ของท่าน > กด `Advanced (ขั้นสูง)` > กด `Go to ... (unsafe)` > กด `Allow (อนุญาต)`
6. คัดลอก **URL ของเว็บแอป (Web App URL)** (จะมีลักษณะเป็น `https://script.google.com/macros/s/.../exec`)

### ขั้นตอนที่ 4: ใส่ URL ในหน้าเว็บ
1. เปิดหน้าเว็บ `http://localhost/cshdonation`
2. เลื่อนลงมาด้านล่างสุด คลิกปุ่ม **"ตั้งค่า Google Sheet / สถานะระบบ (Admin)"**
3. วาง Web App URL ที่คัดลอกมาลงในช่อง แล้วกดปุ่ม **"ทดสอบการเชื่อมต่อ"**
4. เมื่อขึ้นว่าเชื่อมต่อสำเร็จ ให้กด **"บันทึกการตั้งค่า"** เป็นอันเสร็จสมบูรณ์!

---

## 📁 โครงสร้างไฟล์
```text
cshdonation/
├── images/
│   ├── hospital.png           # โลโก้โรงพยาบาล
│   ├── donation-qr.jpg        # รูป QR Code e-Donation
│   └── donation-qr.svg        # SVG QR Code
├── google-apps-script/
│   └── Code.gs                # โค้ดสำหรับวางใน Google Sheets Apps Script
├── api/
│   ├── config.php             # ไฟล์จัดการการตั้งค่า
│   ├── submit.php             # API รับข้อมูลบริจาคและส่งต่อ Google Sheet
│   ├── test_connection.php    # API ทดสอบการเชื่อมต่อ Google Sheet
│   ├── get_config.php         # API ดึงสถานะระบบ
│   ├── save_config.php        # API บันทึกการตั้งค่า
│   └── get_wishes.php         # API ดึงข้อความให้กำลังใจมาแสดง
├── data/
│   ├── config.json            # ไฟล์เก็บ URL และการตั้งค่า
│   └── donations_backup.json  # ไฟล์สำรองข้อมูลการบริจาค
├── uploads/                   # โฟลเดอร์สำรองรูปภาพที่ผู้บริจาคอัปโหลด
├── index.php                  # หน้าเว็บหลัก (PHP + Dynamic Settings)
├── index.html                 # หน้าเว็บสำรอง (Standalone HTML)
├── config.js                  # ตั้งค่า Client-side
└── README.md                  # คู่มือการใช้งานนี้
```

---

## 💻 วิธีการเปิดใช้งานบน XAMPP
เปิดเบราว์เซอร์ไปที่:
```
http://localhost/cshdonation
```
หรือหากเปิดผ่านพอร์ตอื่น เช่น:
```
http://localhost:8000/cshdonation
```
