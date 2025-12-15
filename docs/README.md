# PKD Server Documentation

For the sake of simpliity, we've separated the documentation for the PKD Server software into multiple subdirectories,
each with a distinct intended audience.

* **[Operators Manual](operators)**:  
  This section is intended for users that wish to deploy a Public Key Directory instance.
* **[Developer Guide](developers)**:  
  This section is intended for developers that wish to contribute to this project.
* **[Technical Reference](reference)**:  
  This section contains technical information without a specific audience in mind.

If the documentation contains any errors or omissions, please understand that we're writing it by hand (or by paw), not
with large language models.

## Where is the cryptography?

We extracted most cryptographic features into [fedi-e2ee/pkd-crypto](https://github.com/fedi-e2ee/pkd-crypto) for ease
of reuse. 

The cryptographic algorithms and protocols used are documented in the 
[specification](https://github.com/fedi-e2ee/public-key-directory-specification).
