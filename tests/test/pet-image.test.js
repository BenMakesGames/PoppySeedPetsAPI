const fs = require('fs');
const path = require('path');

describe('Pet images', function() {
    it('are all colorable', async () => {
        const path = '../PoppySeedPetsApp/src/assets/images/pets';

        // find all .svg files in subdirs
        const files = Array.from(readAllFiles(path))
            .filter(file => file.endsWith('.svg'));

        // if no files were found, fail the test
        if (files.length === 0) {
            throw new Error('No files found');
        }

        // read each file
        for (const file of files) {
            const data = fs.readFileSync(file, 'utf8');

            // make sure the text "colorA" appears
            if (!data.includes('colorA')) {
                throw new Error(`File ${file} is not colorable`);
            }
        }
    });
});

function* readAllFiles(dir)
{
    const files = fs.readdirSync(dir, { withFileTypes: true });

    for (const file of files) {
        if (file.isDirectory()) {
            yield* readAllFiles(path.join(dir, file.name));
        } else {
            yield path.join(dir, file.name);
        }
    }
}