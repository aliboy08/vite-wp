import { v4wp } from "./src/vite-wp-plugin/vite-wp-plugin"

export default {
	plugins: [
		v4wp( {
			input: {
                critical: 'src/critical.js',
                main: 'src/main.js',
			},
			outDir: 'dist',
		} ),
        // {
        //     name: 'override-config',
        //     config: () => ({
        //         build: {
        //             // ensure that manifest.json is not in ".vite/" folder
        //             // manifest: 'manifest.json',
        //         },
        //     }),
        // },
	],
};
