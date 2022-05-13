#!/bin/bash

# exit if any command fails, we don't want to continue the process if something fails
set -euf -o pipefail

# get a hash from current git
TAG_HASH=$(/usr/bin/git log -1 --pretty=%h)
REPO=https://docker-registry.bigmike.dev/projects-api

echo "Building for: $TAG_HASH"

# remove previous image if it exists
echo "Removing previous image... docker rmi $REPO:$TAG_HASH"
docker rmi $REPO:$TAG_HASH || true

# build docker image
echo "Building app container..."
docker build --no-cache --file Dockerfile --tag $REPO:$TAG_HASH ../

# push to repo
echo "Pushing container to repo..."
docker push $REPO:$TAG_HASH
echo "Image built and pushed. To deploy the services, run the commands below:"
echo "--------------------------------------------------------"
echo ""
echo "--------------------------------------------------------"
echo "To run locally for testing:"
echo "--------------------------------------------------------"
echo "docker run -p 8080:80 $REPO:$TAG_HASH"
