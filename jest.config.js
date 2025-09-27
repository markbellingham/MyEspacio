/** @type {import('jest').Config} */
module.exports = {
    preset: "ts-jest",
    testEnvironment: "jsdom",
    roots: ["<rootDir>/tests/Ts"],
    verbose: false,
    bail: false,
    collectCoverage: true,
    collectCoverageFrom: [
        "web/ts/**/*.{ts,tsx}",
        "!web/ts/**/*.d.ts"
    ],
    coverageDirectory: "<rootDir>/coverage",
    coverageReporters: ["text", "lcov", "clover"],
    testMatch: ["**/*.test.ts?(x)"],
    transform: {
        "^.+\\.tsx?$": [
            "ts-jest",
            { tsconfig: "<rootDir>/tsconfig.jest.json" }
        ],
    },
};
