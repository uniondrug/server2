# in docker

```bash

docker run --rm -it \
    --name sketch \
    -v ~/SourceCodes/uniondrug-2018/sketch:/uniondrug/app \
    uniondrug:base \
    bash

```