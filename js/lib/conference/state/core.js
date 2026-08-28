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
export let delayRemote = 5000;

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

// Exposer sur window pour compatibilité legacy
if (typeof window !== 'undefined') {
  window.timenew_question = timenew_question;
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
  if (typeof window !== 'undefined') {
    window.nbnewQ = value;
  }
}

/**
 * Set the inputnumberQ value
 * @param {number} value - The new value
 */
export function setInputnumberQ(value) {
  inputnumberQ = value;
  if (typeof window !== 'undefined') {
    window.inputnumberQ = value;
  }
}

/**
 * Set the NBconnectedusers value
 * @param {number} value - The new value
 */
export function setNBconnectedusers(value) {
  NBconnectedusers = value;
  if (typeof window !== 'undefined') {
    window.NBconnectedusers = value;
  }
}

/**
 * Set the Tconnectedusers array
 * @param {Array} arr - The new array
 */
export function setTconnectedusers(arr) {
  // Clear and repopulate the array
  Tconnectedusers.length = 0;
  Tconnectedusers.push(...arr);
  if (typeof window !== 'undefined') {
    window.Tconnectedusers = Tconnectedusers;
  }
}

/**
 * Remove a user from Tconnectedusers
 * @param {string} user - The user to remove
 */
export function removeFromTconnectedusers(user) {
  Tconnectedusers = Tconnectedusers.filter((student) => student !== user);
  if (typeof window !== 'undefined') {
    window.Tconnectedusers = Tconnectedusers;
  }
}

/**
 * Set the inputnumber value
 * @param {number} value - The new value
 */
export function setInputnumber(value) {
  inputnumber = value;
  if (typeof window !== 'undefined') {
    window.inputnumber = value;
  }
}

/**
 * Set the FeedbackQueue value
 * @param {number} value - The new queue value
 */
export function setFeedbackQueue(value) {
  FeedbackQueue = value;
  if (typeof window !== 'undefined') {
    window.FeedbackQueue = value;
  }
}

/**
 * Set the delayReadtextQ value
 * @param {number} value - The delay in milliseconds
 */
export function setDelayReadtextQ(value) {
  delayReadtextQ = value;
  if (typeof window !== 'undefined') {
    window.delayReadtextQ = value;
  }
}

/**
 * Set the delayRemote value
 * @param {number} value - The delay in milliseconds
 */
export function setDelayRemote(value) {
  delayRemote = value;
  if (typeof window !== 'undefined') {
    window.delayRemote = value;
  }
}

/**
 * Set the delayStudentReadtext value
 * @param {number} value - The delay in milliseconds
 */
export function setDelayStudentReadtext(value) {
  delayStudentReadtext = value;
  if (typeof window !== 'undefined') {
    window.delayStudentReadtext = value;
  }
}

/**
 * Set the delayFeedback value
 * @param {number} value - The delay in milliseconds
 */
export function setDelayFeedback(value) {
  delayFeedback = value;
  if (typeof window !== 'undefined') {
    window.delayFeedback = value;
  }
}

/**
 * Set the timenew_question value
 * @param {number} value - The new value
 */
export function setTimenewQuestion(value) {
  timenew_question = value;
  if (typeof window !== 'undefined') {
    window.timenew_question = value;
  }
}
