## Contributing

Please make sure to read the [Olifanton contribution guide](https://github.com/olifanton/.github/blob/main/profile/CONTRIBUTING.md) before making a pull request.

### Setup environment

Prepare your environment for development.

Note that the instructions describe working on *nix systems (Linux and possibly macOS),
development on Windows may be complex and will not be covered in these instructions.

You'll need:

1. Minimum PHP version: 8.1;
2. PHP [`sodium`](https://www.php.net/manual/en/intro.sodium.php) extension;
3. PHP [`hash`](https://www.php.net/manual/en/intro.hash.php) extension.

### Fork repository

Make a repository fork in your GitHub account.

### Clone your repository

```bash
git clone git@github.com:<YOUR_GITHUB_NAME>/ton.git
cd ton
```

### Create a `feature/` (or `hotfix/`) branch

```bash
git branch feature/<FEATURE_NAME>
git checkout feature/<FEATURE_NAME>
```

### Create pull request

After implementing your new feature (or hotfix) in your local branch, you should
commit and push changes to your fork repository. After committing and pushing your changes, you can create a pull-request.

---

<p align="right">(<a href="README.md">back to README</a>)</p>