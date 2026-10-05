/**
 * @module lib/download
 * File download utilities
 */

/**
 * Download text content as a file
 * @param {string} filename - The name of the file to download
 * @param {string} elText - The text content to download
 * @param {string} [mimeType='text/plain'] - The MIME type of the file
 */
export function DownloadSpeechLesson(filename, elText, mimeType = 'text/plain') {
  elText = elText.replaceAll('<br>', '\n');
  const link = document.createElement('a');
  link.setAttribute('download', filename);
  link.setAttribute('href', `data:${mimeType};charset=utf-8,${encodeURIComponent(elText)}`);
  link.click();
}
