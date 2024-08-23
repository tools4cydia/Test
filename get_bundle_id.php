<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['ipa_file'])) {
        $ipaFilePath = $_FILES['ipa_file']['tmp_name'];
        $tempDir = sys_get_temp_dir() . '/' . uniqid('ipa_', true);

        // قم بإنشاء مجلد مؤقت لاستخراج محتويات ملف الـ IPA
        mkdir($tempDir);

        // فك ضغط ملف IPA
        $unzipCommand = "unzip -q $ipaFilePath -d $tempDir";
        exec($unzipCommand, $output, $return_var);

        if ($return_var !== 0) {
            echo json_encode(['success' => false, 'message' => 'فشل في فك ضغط ملف IPA']);
            exit();
        }

        // ابحث عن ملف Info.plist
        $infoPlistPath = glob($tempDir . '/Payload/*.app/Info.plist')[0] ?? null;

        if (!$infoPlistPath) {
            echo json_encode(['success' => false, 'message' => 'فشل في العثور على ملف Info.plist']);
            exit();
        }

        // استخراج CFBundleIdentifier باستخدام PlistBuddy
        $bundleIdCommand = "/usr/libexec/PlistBuddy -c 'Print :CFBundleIdentifier' $infoPlistPath";
        $bundleId = trim(shell_exec($bundleIdCommand));

        if (!$bundleId) {
            echo json_encode(['success' => false, 'message' => 'فشل في استخراج اسم البندل']);
        } else {
            echo json_encode(['success' => true, 'bundle_id' => $bundleId]);
        }

        // تنظيف الملفات المؤقتة
        exec("rm -rf $tempDir");
    } else {
        echo json_encode(['success' => false, 'message' => 'لم يتم رفع ملف IPA']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طريقة الطلب غير صحيحة']);
}
?>
