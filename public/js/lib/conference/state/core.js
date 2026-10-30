/**
 * conference/state/core.js
 * Module pour gérer l'état core de l'application conference
 * Variables : utilisateurs, questions, queue, timers
 */

// =============================================================================
// État des utilisateurs connectés
// =============================================================================

/** @type {number} */
let NBconnectedusers = 0;

/** @type {Array} */
let Tconnectedusers = [];

// =============================================================================
// État des questions
// =============================================================================

/** @type {number} */
let nbnewQ = 0;

// =============================================================================
// Chat/Queue State
// =============================================================================

/** @type {number} */
let inputnumberQ = 0;

/** @type {number} */
let inputnumber = 0;

/** @type {number} */
let delayReadtextQ = 5000;

/** @type {number} */
let FeedbackQueue = 0;

/** @type {number} */
let delayStudentReadtext = 1000;

/** @type {number} */
let delayFeedback = 1000;

/** @type {number} */
export const MaxSavedLines = 100;

// =============================================================================
// Timer variables
// =============================================================================

/** @type {number} */
let timenew_question = 0;

// =============================================================================
// Getters (variables privées, lecture externe via fonctions)
// =============================================================================

/**
 * Get the NBconnectedusers value
 * @returns {number} The number of connected users
 */
export function getNBconnectedusers() {
  return NBconnectedusers;
}

/**
 * Get the Tconnectedusers value
 * @returns {Array} The connected users array
 */
export function getTconnectedusers() {
  return Tconnectedusers;
}

/**
 * Get the nbnewQ value
 * @returns {number} The number of new questions
 */
export function getNbnewQ() {
  return nbnewQ;
}

/**
 * Get the inputnumberQ value
 * @returns {number} The input number for questions
 */
export function getInputnumberQ() {
  return inputnumberQ;
}

/**
 * Get the inputnumber value
 * @returns {number} The input number
 */
export function getInputnumber() {
  return inputnumber;
}

/**
 * Get the delayReadtextQ value
 * @returns {number} The read text delay for questions
 */
export function getDelayReadtextQ() {
  return delayReadtextQ;
}

/**
 * Get the FeedbackQueue value
 * @returns {number} The feedback queue count
 */
export function getFeedbackQueue() {
  return FeedbackQueue;
}

/**
 * Get the delayStudentReadtext value
 * @returns {number} The student read text delay
 */
export function getDelayStudentReadtext() {
  return delayStudentReadtext;
}

/**
 * Get the delayFeedback value
 * @returns {number} The feedback delay
 */
export function getDelayFeedback() {
  return delayFeedback;
}

/**
 * Get the timenew_question value
 * @returns {number} The new question timestamp
 */
export function getTimenewQuestion() {
  return timenew_question;
}

// =============================================================================
// Setter functions for core state
// =============================================================================

/**
 * Set the nbnewQ value
 * @param {number} value - The new value
 */
export function setNbnewQ(value) {
  nbnewQ = value;
}

/**
 * Set the inputnumberQ value
 * @param {number} value - The new value
 */
export function setInputnumberQ(value) {
  inputnumberQ = value;
}

/**
 * Set the NBconnectedusers value
 * @param {number} value - The new value
 */
export function setNBconnectedusers(value) {
  NBconnectedusers = value;
}

/**
 * Set the Tconnectedusers array
 * @param {Array} arr - The new array
 */
export function setTconnectedusers(arr) {
  // Clear and repopulate the array
  Tconnectedusers.length = 0;
  Tconnectedusers.push(...arr);
}

/**
 * Remove a user from Tconnectedusers
 * @param {string} user - The user to remove
 */
export function removeFromTconnectedusers(user) {
  Tconnectedusers = Tconnectedusers.filter((student) => student !== user);
}

/**
 * Set the inputnumber value
 * @param {number} value - The new value
 */
export function setInputnumber(value) {
  inputnumber = value;
}

/**
 * Set the FeedbackQueue value
 * @param {number} value - The new queue value
 */
export function setFeedbackQueue(value) {
  FeedbackQueue = value;
}

/**
 * Set the delayReadtextQ value
 * @param {number} value - The delay in milliseconds
 */
export function setDelayReadtextQ(value) {
  delayReadtextQ = value;
}

/**
 * Set the delayStudentReadtext value
 * @param {number} value - The delay in milliseconds
 */
export function setDelayStudentReadtext(value) {
  delayStudentReadtext = value;
}

/**
 * Set the delayFeedback value
 * @param {number} value - The delay in milliseconds
 */
export function setDelayFeedback(value) {
  delayFeedback = value;
}

/**
 * Set the timenew_question value
 * @param {number} value - The new value
 */
export function setTimenewQuestion(value) {
  timenew_question = value;
}
