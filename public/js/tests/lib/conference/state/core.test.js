/**
 * Tests unitaires pour js/lib/conference/state/core.js
 * Tests des setters de l'état core
 */

import { describe, it, expect, beforeEach } from 'vitest';
import {
  setNbnewQ, setInputnumberQ, setNBconnectedusers, setTconnectedusers,
  removeFromTconnectedusers, setInputnumber, setFeedbackQueue,
  setDelayReadtextQ, setDelayStudentReadtext,
  setDelayFeedback, setTimenewQuestion,
  nbnewQ, inputnumberQ, NBconnectedusers, Tconnectedusers,
  inputnumber, FeedbackQueue, delayReadtextQ,
  delayStudentReadtext, delayFeedback, timenew_question,
} from '../../../../lib/conference/state/core.js';

describe('lib/conference/state/core.js - Core State Setters', () => {
  beforeEach(() => {
    // Reset all state to initial values
    setNbnewQ(0);
    setInputnumberQ(0);
    setNBconnectedusers(0);
    setTconnectedusers([]);
    setInputnumber(0);
    setFeedbackQueue(0);
    setDelayReadtextQ(5000);
    setDelayStudentReadtext(1000);
    setDelayFeedback(1000);
    setTimenewQuestion(0);
  });

  describe('setNbnewQ()', () => {
    it('should set nbnewQ value', () => {
      setNbnewQ(5);
      expect(nbnewQ).toBe(5);
    });
  });

  describe('setInputnumberQ()', () => {
    it('should set inputnumberQ value', () => {
      setInputnumberQ(3);
      expect(inputnumberQ).toBe(3);
    });
  });

  describe('setNBconnectedusers()', () => {
    it('should set NBconnectedusers value', () => {
      setNBconnectedusers(42);
      expect(NBconnectedusers).toBe(42);
    });
  });

  describe('setTconnectedusers()', () => {
    it('should set Tconnectedusers array', () => {
      setTconnectedusers(['user1', 'user2']);
      expect(Tconnectedusers).toEqual(['user1', 'user2']);
    });

    it('should replace existing array', () => {
      setTconnectedusers(['user1']);
      setTconnectedusers(['user3', 'user4']);
      expect(Tconnectedusers).toEqual(['user3', 'user4']);
    });
  });

  describe('removeFromTconnectedusers()', () => {
    it('should remove user from Tconnectedusers', () => {
      setTconnectedusers(['user1', 'user2', 'user3']);
      removeFromTconnectedusers('user2');
      expect(Tconnectedusers).toEqual(['user1', 'user3']);
    });

    it('should not modify array if user not found', () => {
      setTconnectedusers(['user1', 'user2']);
      removeFromTconnectedusers('user3');
      expect(Tconnectedusers).toEqual(['user1', 'user2']);
    });
  });

  describe('setInputnumber()', () => {
    it('should set inputnumber value', () => {
      setInputnumber(5);
      expect(inputnumber).toBe(5);
    });
  });

  describe('setFeedbackQueue()', () => {
    it('should set FeedbackQueue value', () => {
      setFeedbackQueue(10);
      expect(FeedbackQueue).toBe(10);
    });
  });

  describe('setDelayReadtextQ()', () => {
    it('should set delayReadtextQ value', () => {
      setDelayReadtextQ(1000);
      expect(delayReadtextQ).toBe(1000);
    });
  });

  describe('setDelayStudentReadtext()', () => {
    it('should set delayStudentReadtext value', () => {
      setDelayStudentReadtext(2000);
      expect(delayStudentReadtext).toBe(2000);
    });
  });

  describe('setDelayFeedback()', () => {
    it('should set delayFeedback value', () => {
      setDelayFeedback(500);
      expect(delayFeedback).toBe(500);
    });
  });

  describe('setTimenewQuestion()', () => {
    it('should set timenew_question value', () => {
      setTimenewQuestion(1234567890);
      expect(timenew_question).toBe(1234567890);
    });
  });
});
