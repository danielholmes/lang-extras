import { assert } from "chai";
import { caughtResultToError, unknownToString } from "./to-string";

describe("toString", () => {
  describe("unknownToString", () => {
    it("should return error message", () => {
      const result = unknownToString(new Error("Some message"));

      assert.equal(result, "Some message");
    });

    it("should return string", () => {
      const result = unknownToString("Some string");

      assert.equal(result, "Some string");
    });

    it("should return number as string", () => {
      const result = unknownToString(1234);

      assert.equal(result, "1234");
    });
  });

  describe("caughtResultToError", () => {
    it("should return same error instance", () => {
      const error = new Error("Some message");

      const result = caughtResultToError(error);

      assert.strictEqual(result, error);
    });

    it("should return string error", () => {
      const result = caughtResultToError("Some string");

      assert.instanceOf(result, Error);
      assert.equal(result.message, "Some string");
    });

    it("should return number as string", () => {
      const result = caughtResultToError(1234);

      assert.instanceOf(result, Error);
      assert.equal(result.message, "1234");
    });
  });
});