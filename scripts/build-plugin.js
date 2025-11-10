import archiver from "archiver";
import fs from "fs-extra";
import path from "path";
import { fileURLToPath } from "url";

const pluginSlug = "zd-guide";
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const distDir = path.join(__dirname, "../dist");
const pluginDir = path.join(distDir, pluginSlug);
const zipPath = path.join(distDir, `${pluginSlug}.zip`);

const includePatterns = [
  "build/**/*",
  "includes/**/*.php",
  "templates/**/*.php",
  "languages/**/*",
  "assets/**/*",
  "zd-guide.php",
  "README.md",
  "readme.txt",
  "LICENSE",
];

const excludePatterns = [
  "**/.DS_Store",
  "**/node_modules/**",
  "**/.git/**",
  "**/phpunit.xml",
  "**/composer.json",
  "**/composer.lock",
];

const buildPlugin = async () => {
  try {
    console.log("🔨 Building WordPress plugin package...");

    await fs.remove(distDir);
    await fs.ensureDir(pluginDir);

    console.log("Copying plugin files...");
    for (const pattern of includePatterns) {
      const sourcePath = path.join(__dirname, "..", pattern);
      const files = await fs.glob(sourcePath);

      for (const file of files) {
        const relativePath = path.relative(path.join(__dirname, ".."), file);
        const shouldExclude = excludePatterns.some((exclude) =>
          new RegExp(
            exclude.replace(/\*\*/g, ".*").replace(/\*/g, "[^/]*")
          ).test(relativePath)
        );

        if (!shouldExclude) {
          const destPath = path.join(pluginDir, relativePath);
          await fs.copy(file, destPath);
        }
      }
    }

    console.log("🗜️  Creating zip archive...");
    await createZip(pluginDir, zipPath);

    console.log(`✅ Plugin package created: ${zipPath}`);
    console.log(`📦 Ready for WordPress installation!`);
  } catch (error) {
    console.error("❌ Build failed:", error);
    process.exit(1);
  }
};

function createZip(sourceDir, outPath) {
  return new Promise((resolve, reject) => {
    const output = fs.createWriteStream(outPath);
    const archive = archiver("zip", { zlib: { level: 9 } });

    output.on("close", () => {
      console.log(
        `Archive size: ${(archive.pointer() / 1024 / 1024).toFixed(2)} MB`
      );
      resolve();
    });

    archive.on("error", reject);
    archive.pipe(output);

    archive.directory(sourceDir, pluginSlug);
    archive.finalize();
  });
}

buildPlugin();
