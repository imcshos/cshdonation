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

    // จัดการคำสั่งจากฝั่ง Admin (toggle_status / delete_row)
    if (data.action === "toggle_status" || data.action === "delete_row") {
      var rowIdx = parseInt(data.row, 10);
      if (!rowIdx || rowIdx < 2 || rowIdx > sheet.getLastRow()) {
        lock.releaseLock();
        return ContentService.createTextOutput(JSON.stringify({
          success: false,
          message: "ไม่พบแถวข้อมูล (แถว " + rowIdx + ")"
        })).setMimeType(ContentService.MimeType.JSON);
      }
      if (data.action === "delete_row") {
        sheet.deleteRow(rowIdx);
        lock.releaseLock();
        return ContentService.createTextOutput(JSON.stringify({
          success: true,
          row: rowIdx,
          message: "ลบข้อมูลแถวที่ " + rowIdx + " สำเร็จ"
        })).setMimeType(ContentService.MimeType.JSON);
      }
      if (data.action === "toggle_status") {
        var currentStatus = String(sheet.getRange(rowIdx, 12).getValue() || "").trim();
        var allowPost = String(sheet.getRange(rowIdx, 9).getValue() || "").trim();
        var newStatus = (currentStatus === "แสดง" || (currentStatus === "" && allowPost === "อนุญาต")) ? "ซ่อน" : "แสดง";
        sheet.getRange(rowIdx, 12).setValue(newStatus);
        lock.releaseLock();
        return ContentService.createTextOutput(JSON.stringify({
          success: true,
          row: rowIdx,
          newStatus: newStatus,
          is_approved: (newStatus === "แสดง"),
          message: "อัปเดตสถานะเป็น " + newStatus + " สำเร็จ"
        })).setMimeType(ContentService.MimeType.JSON);
      }
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

    // 1. ดึงข้อมูลสำหรับหน้า Admin (ทุกแถว)
    if (action === "admin_list") {
      var data = sheet.getDataRange().getValues();
      var list = [];
      var totalDonations = 0;
      var withImagesCount = 0;
      var receiptCount = 0;

      if (data.length > 1) {
        for (var i = 1; i < data.length; i++) {
          var row = data[i];
          if (!row[0] && !row[1] && !row[2]) continue; // ข้ามแถวว่าง
          totalDonations++;
          
          var dateVal = row[0];
          var dateFormatted = "";
          if (dateVal instanceof Date) {
            dateFormatted = Utilities.formatDate(dateVal, "Asia/Bangkok", "dd/MM/yyyy HH:mm น.");
          } else {
            dateFormatted = String(dateVal || "");
          }

          var rRequested = String(row[3] || "").trim();
          var isReceipt = (rRequested === "รับ" || rRequested === "1" || rRequested === "true");
          if (isReceipt) receiptCount++;

          var rawImg1 = String(row[9] || "").trim();
          var rawImg2 = String(row[10] || "").trim();
          if (rawImg1 || rawImg2) withImagesCount++;

          var allowPost = String(row[8] || "").trim();
          var statusVal = String(row[11] || "").trim();
          // ค่าเริ่มต้น: ถ้า allowPost เป็น อนุญาต และไม่ได้ระบุสถานะเป็น ซ่อน ให้ถือว่าแสดง
          var isApproved = (statusVal === "แสดง" || statusVal === "อนุมัติ" || (statusVal !== "ซ่อน" && (allowPost === "อนุญาต" || allowPost === "1")));

          list.push({
            row: i + 1, // 1-based index ใน Google Sheets
            created_at: dateFormatted,
            raw_date: row[0],
            donor_name: row[1] || "ผู้ไม่ประสงค์ออกนาม",
            message: row[2] || "",
            receipt_requested: isReceipt ? "รับ" : "ไม่รับ",
            tax_name: row[4] || "",
            tax_id: String(row[5] || "").replace(/^'/, ""),
            phone: String(row[6] || "").replace(/^'/, ""),
            address: row[7] || "",
            allow_social_post: (allowPost === "อนุญาต" || allowPost === "1") ? "อนุญาต" : "ไม่อนุญาต",
            image1: toDriveDirectUrl(rawImg1),
            image2: toDriveDirectUrl(rawImg2),
            status: statusVal || (isApproved ? "แสดง" : "ซ่อน"),
            is_approved: isApproved
          });
        }
      }

      return ContentService.createTextOutput(JSON.stringify({
        success: true,
        totalDonations: totalDonations,
        withImagesCount: withImagesCount,
        receiptCount: receiptCount,
        donations: list.reverse() // รายการล่าสุดขึ้นก่อน
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 2. สลับสถานะ แสดง/ซ่อน บนหน้าเว็บ (Toggle)
    if (action === "toggle_status") {
      var rowIdx = parseInt(e.parameter.row, 10);
      if (!rowIdx || rowIdx < 2 || rowIdx > sheet.getLastRow()) {
        return ContentService.createTextOutput(JSON.stringify({
          success: false,
          message: "ไม่พบแถวข้อมูลที่ต้องการเปลี่ยนสถานะ (แถว " + rowIdx + ")"
        })).setMimeType(ContentService.MimeType.JSON);
      }

      var currentStatus = String(sheet.getRange(rowIdx, 12).getValue() || "").trim();
      var allowPost = String(sheet.getRange(rowIdx, 9).getValue() || "").trim();

      var newStatus = "แสดง";
      if (currentStatus === "แสดง" || (currentStatus === "" && allowPost === "อนุญาต")) {
        newStatus = "ซ่อน";
      } else {
        newStatus = "แสดง";
      }

      sheet.getRange(rowIdx, 12).setValue(newStatus);
      return ContentService.createTextOutput(JSON.stringify({
        success: true,
        row: rowIdx,
        newStatus: newStatus,
        is_approved: (newStatus === "แสดง"),
        message: "อัปเดตสถานะเป็น " + newStatus + " สำเร็จ"
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 3. ลบแถวข้อมูล (Delete row)
    if (action === "delete_row") {
      var rowIdx = parseInt(e.parameter.row, 10);
      if (!rowIdx || rowIdx < 2 || rowIdx > sheet.getLastRow()) {
        return ContentService.createTextOutput(JSON.stringify({
          success: false,
          message: "ไม่พบแถวข้อมูลที่ต้องการลบ (แถว " + rowIdx + ")"
        })).setMimeType(ContentService.MimeType.JSON);
      }

      sheet.deleteRow(rowIdx);
      return ContentService.createTextOutput(JSON.stringify({
        success: true,
        row: rowIdx,
        message: "ลบข้อมูลรายการแถวที่ " + rowIdx + " เรียบร้อยแล้ว"
      })).setMimeType(ContentService.MimeType.JSON);
    }

    // 4. ดึงเฉพาะข้อความกำลังใจสำหรับหน้าหลัก (Wishes)
    if (action === "wishes" || action === "list") {
      var data = sheet.getDataRange().getValues();
      var wishes = [];
      if (data.length > 1) {
        for (var i = 1; i < data.length; i++) {
          var row = data[i];
          var allowPost = String(row[8] || "").trim();
          var status = String(row[11] || "แสดง").trim();
          var msg = String(row[2] || "").trim();
          var isAllowed = (allowPost === "อนุญาต" || allowPost === "1");
          var isNotHidden = (status !== "ซ่อน");

          if (isAllowed && isNotHidden && msg !== "") {
            var rawImg1 = String(row[9] || "").trim();
            var rawImg2 = String(row[10] || "").trim();

            wishes.push({
              time: row[0],
              name: row[1] || "ผู้มีจิตศรัทธา",
              message: msg,
              image1: toDriveDirectUrl(rawImg1),
              image2: toDriveDirectUrl(rawImg2)
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

/**
 * แปลง Google Drive view URL เป็น Direct image URL
 */
function toDriveDirectUrl(url) {
  if (!url) return "";
  var str = String(url).trim();
  if (!str) return "";
  var match = str.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || str.match(/[?&]id=([a-zA-Z0-9_-]+)/);
  if (match && match[1]) {
    return "https://lh3.googleusercontent.com/d/" + match[1];
  }
  if (str.indexOf("http://") === 0 || str.indexOf("https://") === 0 || str.indexOf("data:image/") === 0) {
    return str;
  }
  return "";
}
