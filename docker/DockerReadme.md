# Building the staff-blog image

## Build

From the `/apps/git/staff-blog` (or `/apps/git/libi` depending where the code is cloned to) directory:

    docker build .

To build and tag:

    docker build -t staff-blog:<TAG> .

**Note**: If the image needs to include new changes in the staff-blog-configuration repository, ensure that latest changes are pull'ed to the sync directory before building the image.

    cd sync
    git checkout <necessary_branch>
    git pull

## Deploying the image to the UMD Nexus Docker Repository

See [Storing images to the UMD Private Docker Registry](https://confluence.umd.edu/display/LIB/Storing+images+to+the+UMD+Private+Docker+Registry)