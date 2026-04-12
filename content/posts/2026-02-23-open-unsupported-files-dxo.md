---
title: Open unsupported old camera files in DxO Photolab
slug: open-unsupported-files-dxo
date: 2026-02-23 10:13
status: published
tags: [photography, dxo photolab]
description: 
---

Going through photos of my dog and found several taken with a Fuji F600exr, a camera not supported by DxO. Just used my own [hack](/open-raw-files-dxo) to solve this inconvenience.

```
for file in *.RAF do
exiftool -M -exif:Model="X-S10" -m -overwrite_original "$file"
```

Done. Files opening without issues, all editing features enabled, can even apply DeepPrime 3 and DeepPrime XD/XD2. All solved by changing one single line of exif.
