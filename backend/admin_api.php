<?php
require_once __DIR__ . '/lib/require_admin.php';
require_once __DIR__ . '/lib/error_handler.php';
require_once __DIR__ . '/../database/config.php';
require_once __DIR__ . '/lib/CourseRepository.php';
require_once __DIR__ . '/lib/StudentRepository.php';

$pdo         = getDB();
$courseRepo  = new CourseRepository($pdo);
$studentRepo = new StudentRepository($pdo);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'list_courses') {
        echo json_encode(['ok' => true, 'data' => $courseRepo->listWithStudentCount()]);
        exit;
    }

    if ($action === 'add_course') {
        $short_name = trim($_POST['short_name'] ?? '');
        $long_key   = trim($_POST['long_key'] ?? '');
        if (!$short_name || !$long_key) {
            echo json_encode(['ok' => false, 'error' => 'ชื่อย่อและชื่อยาวห้ามว่าง']);
            exit;
        }
        $id = $courseRepo->add([
            'short_name'    => $short_name,
            'long_key'      => $long_key,
            'training_date' => trim($_POST['training_date'] ?? ''),
            'year_be'       => trim($_POST['year_be'] ?? ''),
            'verify_url'    => trim($_POST['verify_url'] ?? ''),
        ]);
        echo json_encode(['ok' => true, 'id' => $id]);
        exit;
    }

    if ($action === 'update_course') {
        $id         = (int)($_POST['id'] ?? 0);
        $short_name = trim($_POST['short_name'] ?? '');
        if (!$id || !$short_name) {
            echo json_encode(['ok' => false, 'error' => 'ข้อมูลไม่ครบ']);
            exit;
        }
        $courseRepo->update($id, [
            'short_name'    => $short_name,
            'long_key'      => trim($_POST['long_key'] ?? ''),
            'training_date' => trim($_POST['training_date'] ?? ''),
            'year_be'       => trim($_POST['year_be'] ?? ''),
            'verify_url'    => trim($_POST['verify_url'] ?? ''),
        ]);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'delete_course') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ไม่พบ id']);
            exit;
        }
        $courseRepo->delete($id);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'list_students') {
        $page  = max(1, (int)($_GET['page'] ?? 1));
        $limit = 50;

        $sortKey = $_GET['sort'] ?? 'first_name';
        if (!StudentRepository::isSortable($sortKey)) {
            $sortKey = 'first_name';
        }

        $result = $studentRepo->paginate(
            [
                'course_id' => (int)($_GET['course_id'] ?? 0),
                'year_be'   => trim($_GET['year_be'] ?? ''),
                'q'         => trim($_GET['q'] ?? ''),
            ],
            $sortKey,
            $_GET['dir'] ?? 'asc',
            $page,
            $limit
        );

        echo json_encode([
            'ok'    => true,
            'data'  => $result['data'],
            'total' => $result['total'],
            'page'  => $page,
            'limit' => $limit,
        ]);
        exit;
    }

    if ($action === 'get_student') {
        $id   = (int)($_GET['id'] ?? 0);
        $data = $studentRepo->find($id);
        echo json_encode(['ok' => (bool)$data, 'data' => $data]);
        exit;
    }

    if ($action === 'add_student') {
        $course_id  = (int)($_POST['course_id'] ?? 0);
        $first_name = trim($_POST['first_name'] ?? '');
        if (!$course_id || !$first_name) {
            echo json_encode(['ok' => false, 'error' => 'course_id และชื่อห้ามว่าง']);
            exit;
        }
        $id = $studentRepo->add(array_merge($_POST, [
            'course_id'  => $course_id,
            'first_name' => $first_name,
        ]));
        echo json_encode(['ok' => true, 'id' => $id]);
        exit;
    }

    if ($action === 'update_student') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ไม่พบ id']);
            exit;
        }
        $studentRepo->update($id, $_POST);
        echo json_encode(['ok' => true]);
        exit;
    }

    if ($action === 'delete_student') {
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) {
            echo json_encode(['ok' => false, 'error' => 'ไม่พบ id']);
            exit;
        }
        $studentRepo->delete($id);
        echo json_encode(['ok' => true]);
        exit;
    }

    echo json_encode(['ok' => false, 'error' => 'unknown action']);
} catch (Throwable $e) {
    send_error_response($e, 'เกิดข้อผิดพลาดในการประมวลผล กรุณาลองใหม่อีกครั้ง');
}