import {URLStateManager} from "../../../web/ts/framework/UrlStateManager";

// Mock window.location properly for jsdom
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
    let manager: URLStateManager;

    beforeEach(() => {
        // Reset location to base URL
        mockLocation("http://localhost/");
        
        // Mock history.pushState and replaceState
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

        manager = new URLStateManager();
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
            // Set up initial state with existing parameters
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
            // Set up initial state
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

            // Test unsubscribe
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
});
