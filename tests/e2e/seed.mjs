import { execFileSync } from 'node:child_process';

function wp(args) {
  execFileSync('npx', ['wp-env', 'run', 'cli', 'wp', ...args], { stdio: 'inherit' });
}

wp(['plugin', 'activate', 'digipublish-core']);
wp(['theme', 'activate', 'digipublish']);
wp(['option', 'update', 'blogname', 'DigiPublish Test']);
wp(['rewrite', 'structure', '/%postname%/', '--hard']);
wp(['rewrite', 'flush', '--hard']);

try {
  wp(['term', 'create', 'category', 'Technology', '--slug=technology']);
} catch {}

for (let i = 1; i <= 8; i += 1) {
  const slug = i === 1 ? 'automation-story' : `automation-story-${i}`;
  const title = i === 1 ? 'Automation Story' : `Automation Story ${i}`;
  try {
    wp([
      'post', 'create',
      '--post_type=post',
      '--post_status=publish',
      `--post_title=${title}`,
      `--post_name=${slug}`,
      '--post_excerpt=Representative editorial excerpt for automated browser and accessibility testing.',
      '--post_content=This is representative DigiPublish editorial content used by the automated release-gate environment.',
      '--post_category=1'
    ]);
  } catch {}
}

wp(['option', 'update', 'show_on_front', 'posts']);
