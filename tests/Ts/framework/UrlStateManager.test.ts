import {UrlStateManager} from "../../../web/ts/framework/UrlStateManager";

const mockLocation = (href: string) => {
    const url = new URL(href);
    Object.defineProperty(window, "location", {
        value: {
            href: url.href,
            origin: url.origin,
            pathname: url.pathname,
            search: url.search,
            hash: url.hash,
            toString: () => url.href
        },
        writable: true
    });
};

describe("URLStateManager", () => {
    let manager: UrlStateManager;

    beforeEach(() => {
        mockLocation("http://localhost/");
        jest.spyOn(window.history, "pushState").mockImplementation((_data, _title, url) => {
            if (url) {
                mockLocation(url.toString());
            }
        });
        jest.spyOn(window.history, "replaceState").mockImplementation((_data, _title, url) => {
            if (url) {
                mockLocation(url.toString());
            }
        });
        manager = new UrlStateManager();
        jest.clearAllMocks();
    });

    afterEach(() => {
        manager.destroy();
        jest.restoreAllMocks();
    });

    describe("Parameter management", () => {
        it("should update a single parameter", () => {
            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.updateParam("search", "test");

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/?search=test");
            expect(window.location.search).toBe("?search=test");
        });

        it("should remove parameter when value is null", () => {
            mockLocation("http://localhost/?search=existing&page=1");

            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.updateParam("search", null);

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/?page=1");
            expect(window.location.search).toBe("?page=1");
        });

        it("should update multiple parameters", () => {
            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.updateParams({ search: "test", page: "2" });

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/?search=test&page=2");
            expect(window.location.search).toBe("?search=test&page=2");
        });

        it("should delete parameters with null values", () => {
            mockLocation("http://localhost/?search=test&page=1&filter=active");

            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.updateParams({ search: null, page: "2", filter: "inactive" });

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/?page=2&filter=inactive");
            expect(window.location.search).toBe("?page=2&filter=inactive");
        });
    });

    describe("Parameter retrieval", () => {
        it("should retrieve a single parameter", () => {
            mockLocation("http://localhost/?search=test");

            const value = manager.getParam("search");
            expect(value).toBe("test");
        });

        it("should return null for non-existent parameter", () => {
            mockLocation("http://localhost/?search=test");

            const value = manager.getParam("nonexistent");
            expect(value).toBeNull();
        });

        it("should return all parameters", () => {
            mockLocation("http://localhost/?search=test&page=1");

            const params = manager.getParams();
            expect(params).toEqual({ search: "test", page: "1" });
        });

        it("should return empty object when no parameters exist", () => {
            mockLocation("http://localhost/");

            const params = manager.getParams();
            expect(params).toEqual({});
        });
    });

    describe("Path management", () => {
        it("should update path", () => {
            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.updatePath("/new-path");

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/new-path");
            expect(window.location.pathname).toBe("/new-path");
        });

        it("should get current path", () => {
            mockLocation("http://localhost/test/path");
            
            const path = manager.getCurrentPath();
            expect(path).toBe("/test/path");
        });

        it("should get path segments", () => {
            mockLocation("http://localhost/test/path/segments");
            
            const segments = manager.getPathSegments();
            expect(segments).toEqual(["test", "path", "segments"]);
        });

        it("should return empty array for root path", () => {
            mockLocation("http://localhost/");
            
            const segments = manager.getPathSegments();
            expect(segments).toEqual([]);
        });
    });

    describe("State change callbacks", () => {
        it("should call callback when state changes", () => {
            const callback = jest.fn();
            const unsubscribe = manager.onStateChange(callback);

            manager.updateParam("test", "value");

            expect(callback).toHaveBeenCalledWith(
                expect.any(URLSearchParams),
                "/",
                false
            );

            unsubscribe();
            callback.mockClear();
            manager.updateParam("test", "value2");
            expect(callback).not.toHaveBeenCalled();
        });

        it("should handle callback errors gracefully", () => {
            const errorCallback = jest.fn(() => {
                throw new Error("Test error");
            });
            const normalCallback = jest.fn();

            const consoleSpy = jest.spyOn(console, "error").mockImplementation();

            manager.onStateChange(errorCallback);
            manager.onStateChange(normalCallback);

            manager.updateParam("test", "value");

            expect(consoleSpy).toHaveBeenCalledWith(
                "Error in URL state change callback:",
                expect.any(Error)
            );
            expect(normalCallback).toHaveBeenCalled();

            consoleSpy.mockRestore();
        });
    });

    describe("Utility methods", () => {
        it("should check if parameter exists", () => {
            mockLocation("http://localhost/?search=test");

            expect(manager.hasParam("search")).toBe(true);
            expect(manager.hasParam("nonexistent")).toBe(false);
        });

        it("should get full URL", () => {
            mockLocation("http://localhost/path?search=test");

            const fullURL = manager.getFullURL();
            expect(fullURL).toBe("http://localhost/path?search=test");
        });

        it("should parse URL string", () => {
            const parsed = manager.parseURL("http://example.com/path/to/resource?param=value");

            expect(parsed.path).toBe("/path/to/resource");
            expect(parsed.params.get("param")).toBe("value");
            expect(parsed.segments).toEqual(["path", "to", "resource"]);
        });

        it("should clear all parameters", () => {
            mockLocation("http://localhost/?search=test&page=1");

            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.clearParams();

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/");
            expect(window.location.search).toBe("");
        });
    });

    describe("Combined state operations", () => {
        it("should update both path and parameters with updateState", () => {
            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.updateState("/new-path", { search: "test", page: "2" });

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/new-path?search=test&page=2");
            expect(window.location.pathname).toBe("/new-path");
            expect(window.location.search).toBe("?search=test&page=2");
        });

        it("should update only path with updateState", () => {
            mockLocation("http://localhost/old-path?existing=param");
            const mockPushState = jest.spyOn(window.history, "pushState");
            
            manager.updateState("/new-path");

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/new-path?existing=param");
            expect(window.location.pathname).toBe("/new-path");
            expect(window.location.search).toBe("?existing=param");
        });

        it("should update only parameters with updateState", () => {
            mockLocation("http://localhost/existing-path");
            const mockPushState = jest.spyOn(window.history, "pushState");
            
            manager.updateState(undefined, { search: "test", filter: "active" });

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/existing-path?search=test&filter=active");
            expect(window.location.pathname).toBe("/existing-path");
            expect(window.location.search).toBe("?search=test&filter=active");
        });

        it("should delete parameters with null values in updateState", () => {
            mockLocation("http://localhost/path?search=old&page=1&filter=active");
            const mockPushState = jest.spyOn(window.history, "pushState");
            
            manager.updateState("/new-path", { search: "new", page: null, category: "books" });

            expect(mockPushState).toHaveBeenCalled();
            expect(window.location.pathname).toBe("/new-path");
            
            // Check individual parameters instead of exact URL string order
            const url = new URL(mockPushState.mock.calls[0][2] as string);
            expect(url.searchParams.get("search")).toBe("new");
            expect(url.searchParams.get("filter")).toBe("active");
            expect(url.searchParams.get("category")).toBe("books");
            expect(url.searchParams.has("page")).toBe(false); // Should be deleted
        });

        it("should not update URL if no changes are made with updateState", () => {
            mockLocation("http://localhost/path");
            const mockPushState = jest.spyOn(window.history, "pushState");
            
            manager.updateState("/path", {});

            expect(mockPushState).not.toHaveBeenCalled();
        });
    });

    describe("State retrieval", () => {
        it("should get complete current state", () => {
            mockLocation("http://localhost/test/path/segments?search=value&page=2");
            
            const state = manager.getState();

            expect(state.path).toBe("/test/path/segments");
            expect(state.params.get("search")).toBe("value");
            expect(state.params.get("page")).toBe("2");
            expect(state.segments).toEqual(["test", "path", "segments"]);
        });

        it("should get state with empty parameters", () => {
            mockLocation("http://localhost/path");
            
            const state = manager.getState();

            expect(state.path).toBe("/path");
            expect(Array.from(state.params.entries())).toEqual([]);
            expect(state.segments).toEqual(["path"]);
        });

        it("should get state for root path", () => {
            mockLocation("http://localhost/");
            
            const state = manager.getState();

            expect(state.path).toBe("/");
            expect(Array.from(state.params.entries())).toEqual([]);
            expect(state.segments).toEqual([]);
        });
    });

    describe("Replace state operations", () => {
        it("should replace both path and parameters without adding to history", () => {
            const mockReplaceState = jest.spyOn(window.history, "replaceState");
            const callback = jest.fn();
            manager.onStateChange(callback);
            
            manager.replaceState("/new-path", { search: "test", page: "1" });

            expect(mockReplaceState).toHaveBeenCalledWith(null, "", "http://localhost/new-path?search=test&page=1");
            expect(window.location.pathname).toBe("/new-path");
            expect(window.location.search).toBe("?search=test&page=1");
            expect(callback).toHaveBeenCalledWith(
                expect.any(URLSearchParams),
                "/new-path",
                false
            );
        });

        it("should replace only path with replaceState", () => {
            mockLocation("http://localhost/old-path?existing=param");
            const mockReplaceState = jest.spyOn(window.history, "replaceState");
            
            manager.replaceState("/new-path");

            expect(mockReplaceState).toHaveBeenCalledWith(null, "", "http://localhost/new-path?existing=param");
            expect(window.location.pathname).toBe("/new-path");
            expect(window.location.search).toBe("?existing=param");
        });

        it("should replace only parameters with replaceState", () => {
            mockLocation("http://localhost/existing-path");
            const mockReplaceState = jest.spyOn(window.history, "replaceState");
            
            manager.replaceState(undefined, { search: "test", category: "books" });

            expect(mockReplaceState).toHaveBeenCalledWith(null, "", "http://localhost/existing-path?search=test&category=books");
            expect(window.location.pathname).toBe("/existing-path");
            expect(window.location.search).toBe("?search=test&category=books");
        });

        it("should delete parameters with null values in replaceState", () => {
            mockLocation("http://localhost/path?search=old&page=1&filter=active");
            const mockReplaceState = jest.spyOn(window.history, "replaceState");
            
            manager.replaceState("/path", { search: null, page: "2", filter: "" });

            expect(mockReplaceState).toHaveBeenCalledWith(null, "", "http://localhost/path?page=2");
            expect(window.location.search).toBe("?page=2");
        });
    });

    describe("Navigation operations", () => {
        it("should navigate to new path with parameters", () => {
            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.navigateTo("/products", { category: "books", sort: "name" });

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/products?category=books&sort=name");
            expect(window.location.pathname).toBe("/products");
            expect(window.location.search).toBe("?category=books&sort=name");
        });

        it("should navigate to new path without parameters", () => {
            const mockPushState = jest.spyOn(window.history, "pushState");
            manager.navigateTo("/about");

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/about");
            expect(window.location.pathname).toBe("/about");
            expect(window.location.search).toBe("");
        });

        it("should create absolute URL from path in navigateTo", () => {
            mockLocation("http://localhost/current/path?existing=param");
            const mockPushState = jest.spyOn(window.history, "pushState");
            
            manager.navigateTo("/completely/new/path", { fresh: "start" });

            expect(mockPushState).toHaveBeenCalledWith(null, "", "http://localhost/completely/new/path?fresh=start");
            expect(window.location.pathname).toBe("/completely/new/path");
            expect(window.location.search).toBe("?fresh=start");
        });

        it("should trigger state change callback on navigateTo", () => {
            const callback = jest.fn();
            manager.onStateChange(callback);
            
            manager.navigateTo("/new-section", { tab: "overview" });

            expect(callback).toHaveBeenCalledWith(
                expect.any(URLSearchParams),
                "/new-section",
                false
            );
        });
    });

    describe("Browser navigation handling", () => {
        it("should handle popstate events", () => {
            const callback = jest.fn();
            manager.onStateChange(callback);
            
            // Simulate browser back/forward navigation
            mockLocation("http://localhost/new-path?param=value");
            
            // Trigger the popstate event handler directly
            manager["handlePopStateEvent"]();

            expect(callback).toHaveBeenCalledWith(
                expect.any(URLSearchParams),
                "/new-path",
                false
            );

            const callbackParams = callback.mock.calls[0][0] as URLSearchParams;
            expect(callbackParams.get("param")).toBe("value");
        });

        it("should handle popstate event with no parameters", () => {
            const callback = jest.fn();
            manager.onStateChange(callback);
            
            mockLocation("http://localhost/simple-path");
            
            manager["handlePopStateEvent"]();

            expect(callback).toHaveBeenCalledWith(
                expect.any(URLSearchParams),
                "/simple-path",
                false
            );

            const callbackParams = callback.mock.calls[0][0] as URLSearchParams;
            expect(Array.from(callbackParams.entries())).toEqual([]);
        });

        it("should handle popstate event listener setup and cleanup", () => {
            const addEventListenerSpy = jest.spyOn(window, "addEventListener");
            const removeEventListenerSpy = jest.spyOn(window, "removeEventListener");
            
            const newManager = new UrlStateManager();
            
            expect(addEventListenerSpy).toHaveBeenCalledWith("popstate", expect.any(Function));
            
            newManager.destroy();
            
            expect(removeEventListenerSpy).toHaveBeenCalledWith("popstate", expect.any(Function));
            
            addEventListenerSpy.mockRestore();
            removeEventListenerSpy.mockRestore();
        });

        it("should handle errors in popstate callback gracefully", () => {
            const errorCallback = jest.fn(() => {
                throw new Error("Callback error");
            });
            const normalCallback = jest.fn();
            const consoleSpy = jest.spyOn(console, "error").mockImplementation();

            manager.onStateChange(errorCallback);
            manager.onStateChange(normalCallback);

            manager["handlePopStateEvent"]();

            expect(consoleSpy).toHaveBeenCalledWith(
                "Error in URL state change callback:",
                expect.any(Error)
            );
            expect(normalCallback).toHaveBeenCalled();

            consoleSpy.mockRestore();
        });
    });
});
