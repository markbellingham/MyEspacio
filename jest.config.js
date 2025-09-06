/** @type {import('jest').Config} */
module.exports = {
    preset: "ts-jest",
    testEnvironment: "jsdom",
    roots: ["<rootDir>/tests/Ts"],
    verbose: true,             // show each test suite and test
    bail: false,               // continue running after first failure
    collectCoverage: false,    // optional: enable if you want coverage
    testMatch: ["**/*.test.ts?(x)"], // ensure all .test.ts files are included
    transform: {
        "^.+\\.tsx?$": [
            "ts-jest",
            { tsconfig: "<rootDir>/tsconfig.jest.json" } // your test tsconfig
        ],
    },
};
