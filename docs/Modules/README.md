## Directory structure
The project follows a Modular Hexagonal Architecture approach, where the main modules have a top level directory inside `src`. 

Inside the Module directory is:
* Application - services etc
* Domain - models, collections, etc. Can be further split into
	* Collection
	* Entity - Models
	* Repository - interfaces only
* Infrastructure - classes that directly communicate with data stores, such as a database or API
* Presentation - controllers

