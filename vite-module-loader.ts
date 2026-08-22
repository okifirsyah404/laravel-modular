/* eslint-disable @typescript-eslint/no-unused-vars */
import fs from 'fs/promises';
import path from 'path';
import { pathToFileURL } from 'url';

async function collectModuleAssetsPaths(paths: string[], modulesPath: string) {
    modulesPath = path.join(__dirname, modulesPath);

    const moduleStatusesPath = path.join(__dirname, 'modules_statuses.json');

    try {
        // Read module_statuses.json
        const moduleStatusesContent = await fs.readFile(
            moduleStatusesPath,
            'utf-8',
        );
        const moduleStatuses = JSON.parse(moduleStatusesContent);

        // Read module directories
        const moduleDirectories = await fs.readdir(modulesPath);

        for (const moduleDir of moduleDirectories) {
            if (moduleDir === '.DS_Store') {
                // Skip .DS_Store directory
                continue;
            }

            // Check if the module is enabled (status is true)
            if (moduleStatuses[moduleDir] === true) {
                const viteConfigPath = path.join(
                    modulesPath,
                    moduleDir,
                    'vite.config.ts',
                );

                try {
                    await fs.access(viteConfigPath);
                    // Convert to a file URL for Windows compatibility
                    const moduleConfigURL = pathToFileURL(viteConfigPath);

                    // Import the module-specific Vite configuration
                    const moduleConfig = await import(moduleConfigURL.href);

                    if (
                        moduleConfig.paths &&
                        Array.isArray(moduleConfig.paths)
                    ) {
                        paths.push(...moduleConfig.paths);
                    }
                } catch (err) {
                    // vite.config.js does not exist, skip this module
                    console.warn(
                        `Warning: vite.config.ts not found for module "${moduleDir}". Skipping.`,
                    );
                }
            }
        }
    } catch (error) {
        console.error(
            `Error reading module statuses or module configurations: ${error}`,
        );
    }

    return paths;
}

export default collectModuleAssetsPaths;
