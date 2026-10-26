/**
 * ==============================================================================
 * Google Apps Script สำหรับระบบรับบริจาค โรงพยาบาลเชียงแสน (Chiang Saen Hospital)
 * ==============================================================================
 * 
 * วิธีการติดตั้ง:
 * 1. เปิด Google Sheets เปล่า (สร้างใหม่หรือใช้ที่มีอยู่)
 * 2. ไปที่เมนู ส่วนขยาย (Extensions) > Apps Script
 * 3. ลบโค้ดเดิมทั้งหมดออก แล้ววางโค้ดในไฟล์นี้ลงไป
 * 4. กดปุ่มบันทึก (Save) รูปแผ่นดิสก์
 * 5. กดปุ่ม "ทำให้ใช้งานได้" (Deploy) > "การทำให้ใช้งานได้ใหม่" (New deployment)
 * 6. เลือกประเภท: "เว็บแอป" (Web app)
 *    - คำอธิบาย: CSH Donation API
 *    - ดำเนินการในฐานะ: ตัวฉัน (Me)
 *    - ผู้ที่มีสิทธิ์เข้าถึง: ทุกคน (Anyone)  <-- สำคัญมาก! ต้องเลือก Anyone
 * 7. กด "ทำให้ใช้งานได้" (Deploy) แล้วอนุญาตสิทธิ์การเข้าถึง (Authorize access)
 * 8. คัดลอก "URL ของเว็บแอป" (Web App URL) นำไปใส่ในไฟล์ config.php หรือ config.js
 * ==============================================================================
 */

function doPost(e) {
  try {
    var lock = LockService.getScriptLock();
    // รอ Lock ไม่เกิน 10 วินาทีเพื่อป้องกันข้อมูลชนกันเมื่อมีการส่งพร้อมกัน
    lock.tryLock(10000);

    var data = {};
    if (e && e.postData && e.postData.contents) {
      try {
        data = JSON.parse(e.postData.contents);
      } catch (err) {
        data = e.parameter || {};
      }
    } else if (e && e.parameter) {
      data = e.parameter;
    }

    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheetName = "ข้อมูลบริจาค";
    var sheet = ss.getSheetByName(sheetName);
    
    if (!sheet) {
      sheet = ss.getActiveSheet();
      try {
        sheet.setName(sheetName);
      } catch(e) {}
    }

    // ตรวจสอบและสร้างหัวตาราง (Headers) หากเป็นชีตว่าง
    if (sheet.getLastRow() === 0) {
      var headers = [
        "วันที่-เวลา",
        "ชื่อผู้ร่วมบริจาค",
        "ข้อความ/คำอวยพร",
        "ขอรับใบอนุโมทนาบัตร",
        "ชื่อ-สกุล/นิติบุคคลลดหย่อนภาษี",
        "เลขบัตรประชาชน/เลขผู้เสียภาษี",
        "เบอร์โทรศัพท์",
        "ที่อยู่จัดส่งเอกสาร",
        "อนุญาตเผยแพร่ข้อมูล",
        "รูปภาพที่ 1 (ภาพถ่าย)",
        "รูปภาพที่ 2 (สลิปโอนเงิน)",
        "สถานะ"
      ];
      sheet.appendRow(headers);
      var headerRange = sheet.getRange(1, 1, 1, headers.length);
      headerRange.setFontWeight("bold");
      headerRange.setBackground("#1a5c3a");
      headerRange.setFontColor("#ffffff");
      headerRange.setHorizontalAlignment("center");
      headerRange.setVerticalAlignment("middle");
      sheet.setRowHeight(1, 36);
      sheet.setFrozenRows(1);
    }

    // จัดการอัปโหลดรูปภาพลง Google Drive (ถ้ามีแนบมา)
    var image1Url = "";
    var image2Url = "";
    var folder = null;

    if (data.image1_base64 || data.image2_base64) {
      folder = getOrCreateUploadFolder("CSH_Donations_Uploads");
    }

    var now = new Date();
    var timestampStr = Utilities.formatDate(now, "Asia/Bangkok", "yyyy-MM-dd HH:mm:ss");
    var fileTimePrefix = Utilities.formatDate(now, "Asia/Bangkok", "yyyyMMdd_HHmmss");

    if (data.image1_base64) {
      var fname1 = data.image1_name || ("photo_" + fileTimePrefix + ".jpg");
      image1Url = saveBase64Image(folder, data.image1_base64, fname1);
    }

    if (data.image2_base64) {
      var fname2 = data.image2_name || ("slip_" + fileTimePrefix + ".jpg");
      image2Url = saveBase64Image(folder, data.image2_base64, fname2);
    }

    var receiptReq = (data.receipt_requested == "1" || data.receipt_requested === true || data.receipt_requested == "รับ") ? "รับ" : "ไม่รับ";
    var allowSocial = (data.allow_social_post == "1" || data.allow_social_post === true || data.allow_social_post == "อนุญาต") ? "อนุญาต" : "ไม่อนุญาต";

    var row = [
      timestampStr,
      data.donor_name ? data.donor_name.trim() : "ผู้ไม่ประสงค์ออกนาม",
      data.message ? data.message.trim() : "",
      receiptReq,
      data.tax_name ? data.tax_name.trim() : "",
      data.tax_id ? ("'" + data.tax_id.trim()) : "",
      data.phone ? ("'" + data.phone.trim()) : "",
      data.address ? data.address.trim() : "",
      allowSocial,
      image1Url,
      image2Url,
      "รอดำเนินการ"
    ];

    sheet.appendRow(row);
    
    // ตั้งค่า Format แถวใหม่
    var newRowIdx = sheet.getLastRow();
    sheet.getRange(newRowIdx, 1).setHorizontalAlignment("center");
    sheet.getRange(newRowIdx, 4).setHorizontalAlignment("center");
    sheet.getRange(newRowIdx, 9).setHorizontalAlignment("center");
    sheet.getRange(newRowIdx, 12).setHorizontalAlignment("center");

    lock.releaseLock();

    return ContentService.createTextOutput(JSON.stringify({
      success: true,
      message: "บันทึกข้อมูลร่วมทำบุญเข้า Google Sheet เรียบร้อยแล้ว ขอขอบพระคุณเป็นอย่างยิ่งค่ะ",
      row: newRowIdx,
      timestamp: timestampStr
    })).setMimeType(ContentService.MimeType.JSON);

  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({
      success: false,
      message: "เกิดข้อผิดพลาดในการบันทึก: " + error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}

function doGet(e) {
  try {
    var action = (e && e.parameter && e.parameter.action) ? e.parameter.action : "status";
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheetName = "ข้อมูลบริจาค";
    var sheet = ss.getSheetByName(sheetName) || ss.getActiveSheet();

    if (action === "wishes" || action === "list") {
      var data = sheet.getDataRange().getValues();
      var wishes = [];
      if (data.length > 1) {
        for (var i = 1; i < data.length; i++) {
          var row = data[i];
          var allowPost = String(row[8]);
          var msg = String(row[2] || "").trim();
          if ((allowPost === "อนุญาต" || allowPost === "1") && msg !== "") {
            wishes.push({
              time: row[0],
              name: row[1] || "ผู้มีจิตศรัทธา",
              message: msg,
              image1: row[9] || "",
              image2: row[10] || ""
            });
          }
        }
      }
      return ContentService.createTextOutput(JSON.stringify({
        success: true,
        wishes: wishes.reverse().slice(0, 30)
      })).setMimeType(ContentService.MimeType.JSON);
    }

    return ContentService.createTextOutput(JSON.stringify({
      success: true,
      status: "connected",
      sheetName: sheet.getName(),
      totalDonations: Math.max(0, sheet.getLastRow() - 1),
      spreadsheetTitle: ss.getName(),
      timestamp: Utilities.formatDate(new Date(), "Asia/Bangkok", "yyyy-MM-dd HH:mm:ss")
    })).setMimeType(ContentService.MimeType.JSON);

  } catch (error) {
    return ContentService.createTextOutput(JSON.stringify({
      success: false,
      message: error.toString()
    })).setMimeType(ContentService.MimeType.JSON);
  }
}

/**
 * บันทึกรูปภาพ Base64 ลงใน Google Drive
 */
function saveBase64Image(folder, base64String, fileName) {
  try {
    var cleanBase64 = base64String;
    var mimeType = "image/jpeg";

    if (base64String.indexOf(";base64,") !== -1) {
      var parts = base64String.split(";base64,");
      mimeType = parts[0].replace("data:", "");
      cleanBase64 = parts[1];
    }

    var decoded = Utilities.base64Decode(cleanBase64);
    var blob = Utilities.newBlob(decoded, mimeType, fileName);
    var file = folder.createFile(blob);
    
    // ตั้งค่าให้อนุญาตเปิดดูรูปภาพได้ผ่านลิงก์
    file.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
    return file.getUrl();
  } catch (err) {
    return "Error saving image: " + err.toString();
  }
}

/**
 * ดึงหรือสร้างโฟลเดอร์สำหรับเก็บภาพใน Google Drive
 */
function getOrCreateUploadFolder(folderName) {
  var folders = DriveApp.getFoldersByName(folderName);
  if (folders.hasNext()) {
    return folders.next();
  }
  var newFolder = DriveApp.createFolder(folderName);
  newFolder.setSharing(DriveApp.Access.ANYONE_WITH_LINK, DriveApp.Permission.VIEW);
  return newFolder;
}
