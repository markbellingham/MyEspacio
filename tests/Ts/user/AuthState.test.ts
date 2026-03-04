import AuthState from "../../../web/ts/user/AuthState";

const makeButton = (task: string): HTMLButtonElement => {
    const button = document.createElement("button");
    button.dataset.task = task;
    return button;
};

describe("AuthState", () => {
    describe("initial state from DOM", () => {
        it("should be logged out when task is 'login'", () => {
            const authState = new AuthState(makeButton("login"));
            expect(authState.isLoggedIn()).toBe(false);
        });

        it("should be logged in when task is 'logout'", () => {
            const authState = new AuthState(makeButton("logout"));
            expect(authState.isLoggedIn()).toBe(true);
        });

        it("should be logged out when task is missing", () => {
            const authState = new AuthState(makeButton(""));
            expect(authState.isLoggedIn()).toBe(false);
        });
    });

    describe("setLoggedIn", () => {
        let authState: AuthState;

        beforeEach(() => {
            authState = new AuthState(makeButton(""));
        });

        it("should set the logged in state to true", () => {
            authState.setLoggedIn("Mark");
            expect(authState.isLoggedIn()).toBe(true);
        });

        it("should set the username", () => {
            authState.setLoggedIn("James");
        });

        it("should update the username if called again", () => {
            authState.setLoggedIn("Alice");
            expect(authState.getUsername()).toBe("Alice");

            authState.setLoggedIn("Bob");
            expect(authState.getUsername()).toBe("Bob");
        });
    });

    describe("setLoggedOut", () => {
        let authState: AuthState;

        beforeEach(() => {
            authState = new AuthState(makeButton(""));
        });

        it("should set the logged in state to false", () => {
            authState.setLoggedIn("Mark");
            expect(authState.isLoggedIn()).toBe(true);

            authState.setLoggedOut();
            expect(authState.isLoggedIn()).toBe(false);
        });

        it("should clear the username", () => {
            authState.setLoggedIn("Mark");
            expect(authState.getUsername()).toBe("Mark");

            authState.setLoggedOut();
            expect(authState.getUsername()).toBeNull();
        });

        it("should be safe to call when already logged out", () => {
            expect(() => authState.setLoggedOut()).not.toThrow();
            authState.setLoggedOut();
            expect(authState.isLoggedIn()).toBe(false);
        });
    });
});
