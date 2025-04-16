/** @returns {Promise<import('jest').Config>} */
module.exports = async () => {
    return {
        verbose: true,
        preset: "ts-jest",
        testEnvironment: "jsdom", // So we can use document, etc.
        roots: ["<rootDir>/web/ts/_tests_"],
    };
};
