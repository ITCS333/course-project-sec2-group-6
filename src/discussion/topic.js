/*
  Requirement: Populate the single topic page and manage replies.
 
  Instructions:
  1. This file is already linked to `topic.html` via:
         <script src="topic.js" defer></script>
 
  2. The following ids must exist in topic.html (already listed in the
     HTML comments):
       #topic-subject        — <h1>
       #original-post        — <article>
       #op-message           — <p>    inside #original-post
       #op-footer            — <footer> inside #original-post
       #reply-list-container — <div>
       #reply-form           — <form>
       #new-reply            — <textarea>
 
  3. Implement the TODOs below.
 
  API base URL: ./api/index.php
  Topic object shape returned by the API (from the topics table):
    {
      id:         number,   // integer primary key from the topics table
      subject:    string,
      message:    string,
      author:     string,
      created_at: string    // "YYYY-MM-DD HH:MM:SS"
    }
 
  Reply object shape returned by the API (from the replies table):
    {
      id:         number,   // integer primary key from the replies table
      topic_id:   number,   // integer FK → topics.id
      text:       string,
      author:     string,
      created_at: string    // "YYYY-MM-DD HH:MM:SS"
    }
*/
 
// --- Global Data Store ---
let currentTopicId = null;
let currentReplies = [];
 
// --- Element Selections ---
// TODO: Select each element by its id:
//   topicSubject, opMessage, opFooter,
//   replyListContainer, replyForm, newReplyText.
const topicSubject = document.getElementById("topic-subject");
const opMessage = document.getElementById("op-message");
const opFooter = document.getElementById("op-footer");
const replyListContainer = document.getElementById("reply-list-container");
const replyForm = document.getElementById("reply-form");
const newReplyText = document.getElementById("new-reply");
 
// --- Functions ---
 
/**
 * TODO: Implement getTopicIdFromURL.
 *
 * It should:
 * 1. Read window.location.search.
 * 2. Construct a URLSearchParams object from it.
 * 3. Return the value of the 'id' parameter (a string that represents
 *    the integer primary key of the topic).
 */
function getTopicIdFromURL() {

  const params = new URLSearchParams(window.location.search);
  return params.get("id");
}
 
/**
 * TODO: Implement renderOriginalPost.
 *
 * Parameters:
 *   topic — the topic object returned by the API (see shape above).
 *
 * It should:
 * 1. Set topicSubject.textContent = topic.subject.
 * 2. Set opMessage.textContent    = topic.message.
 * 3. Set opFooter.textContent     = "Posted by: " + topic.author +
 *    " on " + topic.created_at.
 *    (Note: use topic.created_at, which matches the SQL column name.)
 */
function renderOriginalPost(topic) {

  topicSubject.textContent = topic.subject;
  opMessage.textContent = topic.message;
  opFooter.textContent = `Posted by: ${topic.author} on ${topic.created_at}`;
}
 
/**
 * TODO: Implement createReplyArticle.
 *
 * Parameters:
 *   reply — one reply object from the API:
 *     { id, topic_id, text, author, created_at }
 *
 * Returns an <article> element:
 *   <article>
 *     <p>{reply.text}</p>
 *     <footer>Posted by: {reply.author} on {reply.created_at}</footer>
 *     <div>
 *       <button class="delete-reply-btn" data-id="{id}">Delete</button>
 *     </div>
 *   </article>
 *
 * Note: use reply.created_at (not a field called "date") — this matches
 * the SQL column name.
 */
function createReplyArticle(reply) {
  // Create the main article element
  const article = document.createElement("article");
 
  // Create the paragraph with the reply text
  const p = document.createElement("p");
  p.textContent = reply.text;
 
  // Create the footer with author and date
  const footer = document.createElement("footer");
  footer.textContent = `Posted by: ${reply.author} on ${reply.created_at}`;
 
  // Create the action buttons container
  const buttonsDiv = document.createElement("div");
 
  // Create Delete button
  const deleteBtn = document.createElement("button");
  deleteBtn.className = "delete-reply-btn";
  deleteBtn.setAttribute("data-id", reply.id);
  deleteBtn.textContent = "Delete";
 
  // Append button to the div
  buttonsDiv.appendChild(deleteBtn);
 
  // Append all elements to the article
  article.appendChild(p);
  article.appendChild(footer);
  article.appendChild(buttonsDiv);
 
  return article;
}
 
/**
 * TODO: Implement renderReplies.
 *
 * It should:
 * 1. Clear replyListContainer (set innerHTML to "").
 * 2. Loop through currentReplies.
 * 3. For each reply, call createReplyArticle(reply) and append the
 *    result to replyListContainer.
 */
function renderReplies() {
  // Clear the container
  replyListContainer.innerHTML = "";
 
  // Loop through replies and render each one
  currentReplies.forEach(reply => {
    const article = createReplyArticle(reply);
    replyListContainer.appendChild(article);
  });
}
 
/**
 * TODO: Implement handleAddReply (async).
 *
 * This is the event handler for replyForm's 'submit' event.
 * It should:
 * 1. Call event.preventDefault().
 * 2. Read and trim the value from newReplyText (#new-reply).
 * 3. If the value is empty, return early (do nothing).
 * 4. Send a POST to './api/index.php?action=reply' with the body:
 *      {
 *        topic_id: currentTopicId,   // integer
 *        author:   "Student",        // hardcoded for this exercise
 *        text:     replyText
 *      }
 *    The API inserts a row into the replies table.
 * 5. On success (result.success === true):
 *    - Push the new reply object (from result.data) onto currentReplies.
 *    - Call renderReplies() to refresh the list.
 *    - Clear newReplyText.
 */
async function handleAddReply(event) {

  event.preventDefault();
 
  // Read and trim the reply text
  const replyText = newReplyText.value.trim();
 
  // If empty, return early
  if (replyText === "") {
    return;
  }
 
  try {
    // Send POST request to API
    const response = await fetch("./api/index.php?action=reply", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({
        topic_id: currentTopicId,
        author: "Student",
        text: replyText
      })
    });
 
    const result = await response.json();
 
    // Check if the request was successful
    if (result.success === true) {
      // Add the new reply to the global currentReplies array
      currentReplies.push(result.data);
 
      // Re-render the replies list
      renderReplies();
 
      // Clear the textarea
      newReplyText.value = "";
    } else {
      console.error("Failed to add reply:", result);
    }
  } catch (error) {
    console.error("Error adding reply:", error);
  }
}
 
/**
 * TODO: Implement handleReplyListClick (async).
 *
 * This is a delegated click listener on replyListContainer.
 * It should:
 * 1. If event.target has class "delete-reply-btn":
 *    a. Read the integer id from event.target.dataset.id.
 *    b. Send a DELETE to './api/index.php?action=delete_reply&id=<id>'.
 *    c. On success, remove the reply from currentReplies and call
 *       renderReplies().
 */
async function handleReplyListClick(event) {
  // Handle Delete button
  if (event.target.classList.contains("delete-reply-btn")) {
    const id = parseInt(event.target.dataset.id);
 
    try {
      // Send DELETE request to API
      const response = await fetch(`./api/index.php?action=delete_reply&id=${id}`, {
        method: "DELETE"
      });
 
      const result = await response.json();
 
      // Check if the request was successful
      if (result.success === true) {
        // Remove the reply from the global currentReplies array
        currentReplies = currentReplies.filter(reply => reply.id !== id);
 
        // Re-render the replies list
        renderReplies();
      } else {
        console.error("Failed to delete reply:", result);
      }
    } catch (error) {
      console.error("Error deleting reply:", error);
    }
  }
}
 
/**
 * TODO: Implement initializePage (async).
 *
 * It should:
 * 1. Call getTopicIdFromURL() and store the result in currentTopicId.
 * 2. If currentTopicId is null or empty, set
 *    topicSubject.textContent = "Topic not found." and return.
 * 3. Fetch both the topic details and its replies in parallel using
 *    Promise.all:
 *      - Topic:   GET ./api/index.php?id={currentTopicId}
 *                 Response: { success: true, data: { ...topic object } }
 *      - Replies: GET ./api/index.php?action=replies&topic_id={currentTopicId}
 *                 Response: { success: true, data: [ ...reply objects ] }
 *    Replies are stored in the replies table
 *    (columns: id, topic_id, text, author, created_at).
 * 4. Store the replies array in currentReplies
 *    (use an empty array if none exist).
 * 5. If the topic was found:
 *    - Call renderOriginalPost(topic).
 *    - Call renderReplies().
 *    - Attach the 'submit' listener to replyForm (calls handleAddReply).
 *    - Attach a 'click' listener to replyListContainer
 *      (calls handleReplyListClick — event delegation for delete).
 * 6. If the topic was not found:
 *    - Set topicSubject.textContent = "Topic not found."
 */
async function initializePage() {
  
  try {
    // Get the topic ID from the URL
    currentTopicId = getTopicIdFromURL();
 
    // If no topic ID, show error message and return
    if (!currentTopicId) {
      topicSubject.textContent = "Topic not found.";
      return;
    }
 
    // Fetch topic details and replies in parallel
    const [topicResponse, repliesResponse] = await Promise.all([
      fetch(`./api/index.php?id=${currentTopicId}`),
      fetch(`./api/index.php?action=replies&topic_id=${currentTopicId}`)
    ]);
 
    const topicResult = await topicResponse.json();
    const repliesResult = await repliesResponse.json();
 
    // Check if topic was found
    if (topicResult.success === true) {
      const topic = topicResult.data;
 
      // Store replies in currentReplies (use empty array if none exist)
      if (repliesResult.success === true && repliesResult.data) {
        currentReplies = repliesResult.data;
      } else {
        currentReplies = [];
      }
 
      // Render the original post
      renderOriginalPost(topic);
 
      // Render the replies
      renderReplies();
 
      // Attach event listeners
      replyForm.addEventListener("submit", handleAddReply);
      replyListContainer.addEventListener("click", handleReplyListClick);
    } else {
      // Topic not found
      topicSubject.textContent = "Topic not found.";
    }
  } catch (error) {
    console.error("Error initializing page:", error);
    topicSubject.textContent = "Error loading topic.";
  }
}
 
// --- Initial Page Load ---
initializePage();
