/**
 * conference/state/core.js
 * Module pour gérer l'état core de l'application conference
 * Variables : utilisateurs, questions, queue, timers
 */

// =============================================================================
// État des utilisateurs connectés
// =============================================================================

/** @type {number} */
export let NBconnectedusers = 0;

/** @type {Array} */
export let Tconnectedusers = [];

// =============================================================================
// État des questions
// =============================================================================

/** @type {number} */
export let nbnewQ = 0;

// =============================================================================
// Chat/Queue State
// =============================================================================

/** @type {number} */
export let inputnumberQ = 0;

/** @type {number} */
export let inputnumber = 0;

/** @type {number} */
export const Studentinputnumber = 0;

/** @type {number} */
export let delayReadtextQ = 5000;

/** @type {number} */
export let FeedbackQueue = 0;

/** @type {number} */
export let delayStudentReadtext = 1000;

/** @type {number} */
export let delayFeedback = 1000;

/** @type {number} */
export const MaxSavedLines = 100;

// =============================================================================
// Timer variables
// =============================================================================

/** @type {number} */
export let timenew_question = 0;



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
