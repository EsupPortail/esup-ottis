import { FeedbackQueue, NBconnectedusers, Tconnectedusers, builtin_language, builtin_voice, delayFeedback, nbnewQ, delayReadtextQ, delayRemote, delayStudentReadtext, setDelayReadtextQ, setDelayRemote, setDelayStudentReadtext, setFeedbackQueue } from './state.js';

import { readTextQ } from './notebook.js';
import { remote } from './config-functions.js';
import { StudentreadText } from './speech.js';

import { _ } from './dom-helpers.js';
import { addCsrfToken } from './network.js';

/** @module conference/remote */

export function LaunchReadTextQ() {
  readTextQ();
  setTimeout(LaunchReadTextQ, delayReadtextQ);
}

export function LaunchRemote() {
  remote();
  setTimeout(LaunchRemote, delayRemote);
}

export function LaunchStudentReadText() {
  StudentreadText();
  setTimeout(LaunchStudentReadText, delayStudentReadtext);
}

export function LaunchFeedbackReady() {
  if (FeedbackQueue > 0) setFeedbackQueue(FeedbackQueue - delayFeedback); else setFeedbackQueue(0);
  setTimeout(LaunchFeedbackReady, delayFeedback);
}

export function FreeResources() {
  setDelayStudentReadtext(1000);
  setDelayReadtextQ(1000);
  setDelayRemote(1000);
  setTimeout(() => {
    setDelayStudentReadtext(500);
    setDelayReadtextQ(100);
    setDelayRemote(200);
  }, 2000);
}
