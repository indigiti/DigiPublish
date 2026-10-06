import fs from 'node:fs';
import path from 'node:path';
import lighthouse from 'lighthouse';
import * as ChromeLauncher from 'chrome-launcher';
import { chromium } from '@playwright/test';

const url = process.env.DIGIPUBLISH_TEST_URL || 'http://localhost:8888';
process.env.CHROME_PATH = chromium.executablePath();

const chrome = await ChromeLauncher.launch({
  chromeFlags: ['--headless=new', '--no-sandbox', '--disable-gpu']
});

try {
  const result = await lighthouse(url, {
    port: chrome.port,
    logLevel: 'error',
    output: 'json',
    onlyCategories: ['performance', 'accessibility', 'best-practices']
  });

  const report = JSON.parse(result.report);
  const scores = Object.fromEntries(
    Object.entries(report.categories).map(([key, category]) => [key, category.score])
  );

  const thresholds = {
    performance: 0.80,
    accessibility: 0.90,
    'best-practices': 0.90
  };

  fs.mkdirSync(path.resolve('artifacts'), { recursive: true });
  fs.writeFileSync(
    path.resolve('artifacts/lighthouse.json'),
    JSON.stringify(report, null, 2)
  );

  console.log('DigiPublish Lighthouse scores:', scores);

  const failures = Object.entries(thresholds).filter(
    ([key, minimum]) => (scores[key] ?? 0) < minimum
  );

  if (failures.length) {
    for (const [key, minimum] of failures) {
      console.error(`${key}: ${scores[key] ?? 0} is below ${minimum}`);
    }
    process.exitCode = 1;
  }
} finally {
  await chrome.kill();
}
