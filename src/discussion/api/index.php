<?php
/**
 * Discussion Board API
 *
 * RESTful API for CRUD operations on discussion topics and their replies.
 * Uses PDO to interact with the MySQL database defined in schema.sql.
 *
 * Database Tables (ground truth: schema.sql):
 *
 * Table: topics
 *   id         INT UNSIGNED  PRIMARY KEY AUTO_INCREMENT
 *   subject    VARCHAR(255)  NOT NULL
 *   message    TEXT          NOT NULL
 *   author     VARCHAR(100)  NOT NULL
 *   created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
 *
 * Table: replies
 *   id         INT UNSIGNED  PRIMARY KEY AUTO_INCREMENT
 *   topic_id   INT UNSIGNED  NOT NULL — FK → topics.id (ON DELETE CASCADE)
 *   text       TEXT          NOT NULL
 *   author     VARCHAR(100)  NOT NULL
 *   created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
 *
 * HTTP Methods Supported:
 *   GET    — Retrieve topic(s) or replies
 *   POST   — Create a new topic or reply
 *   PUT    — Update an existing topic
 *   DELETE — Delete a topic (cascade removes its replies) or a reply
 *
 * URL scheme (all requests go to index.php):
 *
 *   Topics:
 *     GET    ./api/index.php                  — list all topics
 *     GET    ./api/index.php?id={id}           — get one topic by integer id
 *     POST   ./api/index.php                  — create a new topic
 *     PUT    ./api/index.php                  — update a topic (id in JSON body)
 *     DELETE ./api/index.php?id={id}           — delete a topic
 *
 *   Replies (action parameter selects the replies sub-resource):
 *     GET    ./api/index.php?action=replies&topic_id={id}
 *                                             — list replies for a topic
 *     POST   ./api/index.php?action=reply     — create a reply
 *     DELETE ./api/index.php?action=delete_reply&id={id}
 *                                             — delete a single reply
 *
 * Query parameters for GET all topics:
 *   search — filter rows where subject LIKE or message LIKE or author LIKE
 *   sort   — column to sort by; allowed: subject, author, created_at
 *            (default: created_at)
 *   order  — sort direction; allowed: asc, desc (default: desc)
 *
 * Response format: JSON
 *   Success: { "success": true,  "data": ... }
 *   Error:   { "success": false, "message": "..." }
 */
 
// ============================================================================
// HEADERS AND INITIALIZATION
// ============================================================================
 
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
 
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
 
require_once __DIR__ . '/../../common/db.php';
 
$db = getDBConnection();
 
$method = $_SERVER['REQUEST_METHOD'];
 
$rawData = file_get_contents('php://input');
$data    = json_decode($rawData, true) ?? [];
 
$action  = $_GET['action']   ?? null;
$id      = $_GET['id']       ?? null;
$topicId = $_GET['topic_id'] ?? null;
 
 
// ============================================================================
// TOPICS FUNCTIONS
// ============================================================================
 
/**
 * Get all topics (with optional search and sort).
 * Method: GET (no ?id or ?action parameter).
 *
 * Query parameters handled inside:
 *   search — filter by subject LIKE or message LIKE or author LIKE
 *   sort   — allowed: subject, author, created_at   (default: created_at)
 *   order  — allowed: asc, desc                     (default: desc)
 */
function getAllTopics(PDO $db): void
{
    $query = 'SELECT id, subject, message, author, created_at FROM topics';
 
    $search = $_GET['search'] ?? null;
    if ($search) {
        $search = '%' . $search . '%';
        $query .= ' WHERE subject LIKE :search OR message LIKE :search OR author LIKE :search';
    }
 
    $sort = $_GET['sort'] ?? 'created_at';
    if (!in_array($sort, ['subject', 'author', 'created_at'])) {
        $sort = 'created_at';
    }
 
    $order = $_GET['order'] ?? 'desc';
    if (!in_array(strtolower($order), ['asc', 'desc'])) {
        $order = 'desc';
    }
 
    $query .= ' ORDER BY ' . $sort . ' ' . $order;
 
    $stmt = $db->prepare($query);
    if ($search) {
        $stmt->bindParam(':search', $search, PDO::PARAM_STR);
    }
    $stmt->execute();
 
    $topics = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    sendResponse(['success' => true, 'data' => $topics]);
}
 
 
/**
 * Get a single topic by its integer primary key.
 * Method: GET with ?id={id}.
 *
 * Response (found):
 *   { "success": true, "data": { id, subject, message, author, created_at } }
 * Response (not found): HTTP 404.
 */
function getTopicById(PDO $db, $id): void
{
    if (!$id || !is_numeric($id)) {
        sendResponse(['success' => false, 'message' => 'Invalid topic ID'], 400);
        return;
    }
 
    $stmt = $db->prepare('SELECT id, subject, message, author, created_at FROM topics WHERE id = ?');
    $stmt->execute([$id]);
 
    $topic = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if ($topic) {
        sendResponse(['success' => true, 'data' => $topic]);
    } else {
        sendResponse(['success' => false, 'message' => 'Topic not found'], 404);
    }
}
 
 
/**
 * Create a new topic.
 * Method: POST (no ?action parameter).
 *
 * Required JSON body fields:
 *   subject — string (required)
 *   message — string (required)
 *   author  — string (required)
 *
 * Response (success): HTTP 201 — { success, message, id }
 * Response (missing fields): HTTP 400.
 *
 * Note: id and created_at are handled automatically by MySQL.
 */
function createTopic(PDO $db, array $data): void
{
    $subject = $data['subject'] ?? '';
    $message = $data['message'] ?? '';
    $author = $data['author'] ?? '';
 
    $subject = trim($subject);
    $message = trim($message);
    $author = trim($author);
 
    if (!$subject || !$message || !$author) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: subject, message, author'], 400);
        return;
    }
 
    $stmt = $db->prepare('INSERT INTO topics (subject, message, author) VALUES (?, ?, ?)');
    $result = $stmt->execute([$subject, $message, $author]);
 
    if ($result && $stmt->rowCount() > 0) {
        $id = $db->lastInsertId();
        sendResponse(['success' => true, 'message' => 'Topic created', 'id' => intval($id), 'created_at' => date('Y-m-d H:i:s')], 201);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to create topic'], 500);
    }
}
 
 
/**
 * Update an existing topic.
 * Method: PUT.
 *
 * Required JSON body:
 *   id — integer primary key of the topic to update (required).
 * Optional JSON body fields (at least one must be present):
 *   subject, message.
 *
 * Response (success): HTTP 200.
 * Response (not found): HTTP 404.
 */
function updateTopic(PDO $db, array $data): void
{
    if (!isset($data['id']) || !is_numeric($data['id'])) {
        sendResponse(['success' => false, 'message' => 'Missing or invalid topic ID'], 400);
        return;
    }
 
    $id = intval($data['id']);
 
    $stmt = $db->prepare('SELECT id FROM topics WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Topic not found'], 404);
        return;
    }
 
    $updates = [];
    $values = [];
 
    if (isset($data['subject'])) {
        $updates[] = 'subject = ?';
        $values[] = trim($data['subject']);
    }
 
    if (isset($data['message'])) {
        $updates[] = 'message = ?';
        $values[] = trim($data['message']);
    }
 
    if (empty($updates)) {
        sendResponse(['success' => false, 'message' => 'No fields to update'], 400);
        return;
    }
 
    $values[] = $id;
 
    $query = 'UPDATE topics SET ' . implode(', ', $updates) . ' WHERE id = ?';
    $stmt = $db->prepare($query);
    $result = $stmt->execute($values);
 
    if ($result && $stmt->rowCount() > 0) {
        sendResponse(['success' => true, 'message' => 'Topic updated']);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to update topic'], 500);
    }
}
 
 
/**
 * Delete a topic by integer id.
 * Method: DELETE with ?id={id}.
 *
 * The ON DELETE CASCADE constraint on replies.topic_id automatically
 * removes all replies for this topic — no manual deletion of replies
 * is needed.
 *
 * Response (success): HTTP 200.
 * Response (not found): HTTP 404.
 */
function deleteTopic(PDO $db, $id): void
{
    if (!$id || !is_numeric($id)) {
        sendResponse(['success' => false, 'message' => 'Invalid topic ID'], 400);
        return;
    }
 
    $stmt = $db->prepare('SELECT id FROM topics WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Topic not found'], 404);
        return;
    }
 
    $stmt = $db->prepare('DELETE FROM topics WHERE id = ?');
    $result = $stmt->execute([$id]);
 
    if ($result && $stmt->rowCount() > 0) {
        sendResponse(['success' => true, 'message' => 'Topic deleted']);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to delete topic'], 500);
    }
}
 
 
// ============================================================================
// REPLIES FUNCTIONS
// ============================================================================
 
/**
 * Get all replies for a specific topic.
 * Method: GET with ?action=replies&topic_id={id}.
 *
 * Reads from the replies table.
 * Returns an empty data array if no replies exist — not an error.
 *
 * Each reply object: { id, topic_id, text, author, created_at }
 */
function getRepliesByTopicId(PDO $db, $topicId): void
{
    if (!$topicId || !is_numeric($topicId)) {
        sendResponse(['success' => false, 'message' => 'Invalid topic ID'], 400);
        return;
    }
 
    $stmt = $db->prepare('SELECT id, topic_id, text, author, created_at FROM replies WHERE topic_id = ? ORDER BY created_at ASC');
    $stmt->execute([$topicId]);
 
    $replies = $stmt->fetchAll(PDO::FETCH_ASSOC);
 
    sendResponse(['success' => true, 'data' => $replies]);
}
 
 
/**
 * Create a new reply.
 * Method: POST with ?action=reply.
 *
 * Required JSON body:
 *   topic_id — integer FK into topics.id (required)
 *   text     — string (required, must be non-empty after trim)
 *   author   — string (required)
 *
 * Response (success): HTTP 201 — { success, message, id, data: reply }
 * Response (topic not found): HTTP 404.
 * Response (missing fields): HTTP 400.
 *
 * Note: id and created_at are handled automatically by MySQL.
 */
function createReply(PDO $db, array $data): void
{
    $topic_id = $data['topic_id'] ?? '';
    $text = $data['text'] ?? '';
    $author = $data['author'] ?? '';
 
    $text = trim($text);
    $author = trim($author);
 
    if (!$topic_id || !$text || !$author) {
        sendResponse(['success' => false, 'message' => 'Missing required fields: topic_id, text, author'], 400);
        return;
    }
 
    if (!is_numeric($topic_id)) {
        sendResponse(['success' => false, 'message' => 'Invalid topic_id'], 400);
        return;
    }
 
    $stmt = $db->prepare('SELECT id FROM topics WHERE id = ?');
    $stmt->execute([$topic_id]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Topic not found'], 404);
        return;
    }
 
    $stmt = $db->prepare('INSERT INTO replies (topic_id, text, author) VALUES (?, ?, ?)');
    $result = $stmt->execute([$topic_id, $text, $author]);
 
    if ($result && $stmt->rowCount() > 0) {
        $id = $db->lastInsertId();
        $reply = [
            'id' => intval($id),
            'topic_id' => intval($topic_id),
            'text' => $text,
            'author' => $author,
            'created_at' => date('Y-m-d H:i:s')
        ];
        sendResponse(['success' => true, 'message' => 'Reply created', 'id' => intval($id), 'data' => $reply], 201);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to create reply'], 500);
    }
}
 
 
/**
 * Delete a single reply.
 * Method: DELETE with ?action=delete_reply&id={id}.
 *
 * Response (success): HTTP 200.
 * Response (not found): HTTP 404.
 */
function deleteReply(PDO $db, $replyId): void
{
    if (!$replyId || !is_numeric($replyId)) {
        sendResponse(['success' => false, 'message' => 'Invalid reply ID'], 400);
        return;
    }
 
    $stmt = $db->prepare('SELECT id FROM replies WHERE id = ?');
    $stmt->execute([$replyId]);
    if (!$stmt->fetch()) {
        sendResponse(['success' => false, 'message' => 'Reply not found'], 404);
        return;
    }
 
    $stmt = $db->prepare('DELETE FROM replies WHERE id = ?');
    $result = $stmt->execute([$replyId]);
 
    if ($result && $stmt->rowCount() > 0) {
        sendResponse(['success' => true, 'message' => 'Reply deleted']);
    } else {
        sendResponse(['success' => false, 'message' => 'Failed to delete reply'], 500);
    }
}
 
 
// ============================================================================
// MAIN REQUEST ROUTER
// ============================================================================
 
try {
 
    if ($method === 'GET') {
 
        if ($action === 'replies') {
            getRepliesByTopicId($db, $topicId);
        } elseif ($id) {
            getTopicById($db, $id);
        } else {
            getAllTopics($db);
        }
 
    } elseif ($method === 'POST') {
 
        if ($action === 'reply') {
            createReply($db, $data);
        } else {
            createTopic($db, $data);
        }
 
    } elseif ($method === 'PUT') {
 
        updateTopic($db, $data);
 
    } elseif ($method === 'DELETE') {
 
        if ($action === 'delete_reply') {
            deleteReply($db, $id);
        } else {
            deleteTopic($db, $id);
        }
 
    } else {
        sendResponse(['success' => false, 'message' => 'Method not allowed'], 405);
    }
 
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Database error'], 500);
} catch (Exception $e) {
    error_log('Error: ' . $e->getMessage());
    sendResponse(['success' => false, 'message' => 'Server error'], 500);
}
 
 
// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
 
/**
 * Send a JSON response and stop execution.
 *
 * @param array $data        Must include a 'success' key.
 * @param int   $statusCode  HTTP status code (default 200).
 */
function sendResponse(array $data, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
 
 
/**
 * Sanitize a string input.
 *
 * @param  string $data
 * @return string  Trimmed, tag-stripped, HTML-encoded string.
 */
function sanitizeInput(string $data): string
{
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}
