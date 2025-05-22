* The goal of automated testing is never to get 100% coverage - the goal is to protect you and your fellow devs from making mistakes.
* Don't get too hung up on what the technical definition of a "unit test" is. If your test runs fast, and protects against common/honest mistakes, it's a good test.
* There are a few places tests can go:
  * In code itself, throwing a runtime exception when data is detected to be in an unexpected state
  * In an automated test in this `tests` directory
  * In the build pipeline in the `.github/workflows` directory
  * Use your best judgement; ask other devs; remember the GOAL is to have fast test that protects yourself and other devs from making common mistakes.
